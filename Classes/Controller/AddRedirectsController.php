<?php
declare(strict_types=1);
namespace QcRedirects\Controller;

use Doctrine\DBAL\Driver\Exception;
use LST\BackendModule\Controller\BackendModuleActionController;
use Psr\Http\Message\ServerRequestInterface;
use QcRedirects\Repository\ImportRedirectsRepository;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class AddRedirectsController  extends BackendModuleActionController
{

    const NUMBER_OF_FIELDS = 8;
    const LANG_FILE = 'LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:';
    /**
     * ModuleTemplate object
     *
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * @var LocalizationUtility
     */
    protected $localizationUtility;

    protected array $separatedChars = [
        'tabulation' => "\t",
        'pipe' => '|',
        'semicolon' => ';',
        'colon' => ':',
        'comma' => ',',
    ];

    protected string $selectedSeparatedChar = '';

    protected string $duplicatedSourcePath = '';

    protected ImportRedirectsRepository  $importRedirectsRepository;

    protected $view;

    public function __construct(
        ModuleTemplate $moduleTemplate = null,
        LocalizationUtility $localizationUtility = null,
        StandaloneView $view = null
    )
    {
        $this->localizationUtility = $localizationUtility ?? GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->moduleTemplate = $moduleTemplate ?? GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->view = $view ?? GeneralUtility::makeInstance(StandaloneView::class);
        $this->importRedirectsRepository =  GeneralUtility::makeInstance(ImportRedirectsRepository::class);
    }

    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     * @return void
     * @throws RouteNotFoundException
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
        $this->view->assign('separatedChars', $this->separatedChars);
    }
    public function initializeAction()
    {
        $this->extKey = $this->request->getControllerExtensionKey();
        $this->moduleName = $this->request->getPluginName();
    }


    /**
     * this function is used to receive data form the BE form
     * @param ServerRequestInterface|null $request
     * @return HtmlResponse|null
     * @throws Exception
     */
    public function importAction(ServerRequestInterface $request = null): ?HtmlResponse
    {
        if($request == null){
            return null;
        }
        $redirectsList = $request->getParsedBody()['redirectsList'];
        $this->selectedSeparatedChar = $request->getParsedBody()['separationCharacter'];
        if(!is_null($redirectsList)){
            // convert data to array
            $redirectsListArray = explode("\r\n", $redirectsList);
            $created = $this->processRedirects($redirectsListArray);
            // alert message
            $this->generateAlertMessage($created);
        }
        $this->renderView();
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * This function is used to render View after form submission
     */
    function renderView(){
        $this->view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:qc_redirects/Resources/Private/Templates/Import.html'
        ));
        $this->view->assign('separatedChars', $this->separatedChars);
        $this->moduleTemplate->setContent($this->view->render());
    }

    /**
     * This function returns True if the list was successfully stored in the database
     * @param array $redirectListArray
     * @return bool
     * @throws Exception
     */
    protected function processRedirects(array $redirectListArray): bool
    {
        $validImport = true;
        $rows = [];
        // get the source_paths from the DB
        $sourcePathArray = $this->importRedirectsRepository->getSourcePaths();
        // precessing each line in the array
        foreach ($redirectListArray as $item){
            // separate the row columns
            $row = explode($this->separatedChars[$this->selectedSeparatedChar],$item);
            // remove white spacing
            if($this->selectedSeparatedChar !== "tabulation"){
                $row[2] = preg_replace('/\s+/', '', $row[2]);
                $row[2] = trim($row[2], ' ');
            }
            // empty line
            if(count($row) == 1 && $row[0] == ''){
                continue;
            }
            // verify if all columnus are valide
            // we have to make sure that we have all important fields
            // make sure that all the fields are valid
            if(count($row) == SELF::NUMBER_OF_FIELDS){
                  array_push($rows,[
                    'pid' => '0',
                    'title' => $row[0],
                    'source_host' => $row[1],
                    'source_path' => $row[2],
                    'target' => $row[3],
                    'starttime' => strtotime($row[4]),
                    'endtime' => strtotime($row[5]),
                    'is_regexp' => (int)$row[6],
                    'target_statuscode' => (int)$row[7],
                 ]);

                // verify if the source_path is already exists
                if(in_array($row[2], $sourcePathArray, TRUE)){
                    $validImport = false;
                    $this->duplicatedSourcePath = $row[2];
                    break;
                }
                array_push($sourcePathArray,  $row[2]);
            }
            else{
                $validImport = false;
                break;
            }
        }
        // save the items if all import are valid
        if($validImport && !is_null($rows) && count($rows) > 0){
            $this->importRedirectsRepository->saveRedirects($rows);
            return true;
        }
        return false;
    }

    /**
     * This function is used to generate alert message
     * @param bool $success
     */
    public function generateAlertMessage(bool $success){
        if($success){
            $alertMessageHeader = $this->localizationUtility->translate(SELF::LANG_FILE.'import_success_body');
            $alertMessageBody =    $this->localizationUtility->translate(SELF::LANG_FILE.'success');
            $flashMessageService =  AbstractMessage::OK;
        }
        else{
            $alertMessageBody =    $this->localizationUtility->translate(SELF::LANG_FILE.'error');
            $flashMessageService =   AbstractMessage::ERROR;

            if($this->duplicatedSourcePath == ''){
                $alertMessageHeader = $this->localizationUtility->translate(SELF::LANG_FILE.'import_error_syntax');
            }
            else{
                $alertMessageHeader = $this->localizationUtility->translate(SELF::LANG_FILE.'import_error_duplicated')
                    . ' "'. $this->duplicatedSourcePath .'"'.$this->localizationUtility->translate(SELF::LANG_FILE.'is_duplicated') ;
            }
        }

        $message = GeneralUtility::makeInstance(FlashMessage::class,
            $alertMessageHeader,
            $alertMessageBody,
            $flashMessageService,
        );
        $flashService = GeneralUtility::makeInstance(FlashMessageService::class);
        $messageQueue = $flashService->getMessageQueueByIdentifier();
        $messageQueue->addMessage($message);
    }


}
