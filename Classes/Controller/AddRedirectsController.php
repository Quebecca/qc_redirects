<?php
declare(strict_types=1);
namespace QcRedirects\Controller;

use LST\BackendModule\Controller\BackendModuleActionController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use QcRedirects\Domain\Model\Redirect;
use QcRedirects\Domain\Repository\RedirectRepository;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class AddRedirectsController  extends BackendModuleActionController
{
    const NUMBER_OF_ROW = 8;
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

    /*
     * @var RedirectRepository
     */
    private $redirectRepository;

    protected $separatedChars = [
        'tab' => "\t",
        'pipe' => '|',
        'semicolon' => ';',
        'colon' => ':',
        'comma' => ',',
    ];

    protected $selectedSeparatedChar = '';

    protected  $duplicatedSourcePath = '';

    public function __construct(
        ModuleTemplate $moduleTemplate = null,
        RedirectRepository $redirectRepository = null,
        LocalizationUtility $localizationUtility = null
    )
    {
        $this->localizationUtility = $localizationUtility ?? GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->moduleTemplate = $moduleTemplate ?? GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->redirectRepository = $redirectRepository ?: GeneralUtility::makeInstance(RedirectRepository::class);
    }

    public function initializeAction()
    {
        $this->extKey = $this->request->getControllerExtensionKey();
        $this->moduleName = $this->request->getPluginName();
    }

    /**
     * Import function Handling input variables and rendering the view
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface Response
     */
    public function importAction(ServerRequestInterface $request = null)
    {
        if(is_null($request)){
            return null;
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:qc_redirects/Resources/Private/Templates/Import.html'
        ));

        $redirectsList = $request->getParsedBody()['redirectsList'];
        $character = $request->getParsedBody()['indentationCharacter'];
        $this->selectedSeparatedChar = $request->getParsedBody()['separationCharacter'];
        if(!is_null($redirectsList)){
            // convert data to array
            $redirectsListArray = explode("\r\n", $redirectsList);
            $created = $this->importRedirects($redirectsListArray);
            // alert message
            $this->generateAlertMessage($created);
        }
        $view->assign('character', $character);
        $view->assign('separatedChars', $this->separatedChars);
        $this->moduleTemplate->setContent($view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }


    public function generateAlertMessage(bool $success){
        if($success){
            $alertMessageHeader = $this->localizationUtility->translate(SELF::LANG_FILE.'import_success_body');
            $alertMessageBody =    $this->localizationUtility->translate(SELF::LANG_FILE.'success');
            $flashMessageService =  AbstractMessage::OK;
        }
        else{
            if($this->duplicatedSourcePath == ''){
                $alertMessageHeader = $this->localizationUtility->translate(SELF::LANG_FILE.'import_error_syntax');
                $alertMessageBody =    $this->localizationUtility->translate(SELF::LANG_FILE.'error');
                $flashMessageService =   AbstractMessage::ERROR;
            }
            else{
                $alertMessageHeader = $this->localizationUtility->translate(SELF::LANG_FILE.'import_error_duplicated')
                    . ' "'. $this->duplicatedSourcePath .'"'.$this->localizationUtility->translate(SELF::LANG_FILE.'is_duplicated') ;
                $alertMessageBody =    $this->localizationUtility->translate(SELF::LANG_FILE.'error');
                $flashMessageService =   AbstractMessage::ERROR;
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
    /**
     * Persist imported list of redirects in DB
     *
     * @return bool TRUE if the was list was successfully stored in the database
     */
    protected function importRedirects(array $redirectListArray): bool
    {
        $validImport = true;
        $rows = [];
        // get the source_paths from the DB
        $sourcePathArray = $this->redirectRepository->getSourcePaths();
        // precessing each line in the array
        foreach ($redirectListArray as $item){
            // separate the row columns
            $row = explode($this->separatedChars[$this->selectedSeparatedChar],$item);
            // empty line
            if(count($row) == 1 && $row[0] == ''){
                continue;
            }
            // verify if all columnus are valide
            // we have to make sure that we have all important fields
            // make sure that all the fields are valid
            if(count($row) == SELF::NUMBER_OF_ROW){
                // Mapping item to Demand Redirect Reposiotry
                // title, source host, source path, target, start time, end time, is regular experssion, status code
                /*$redirectItem = new Redirect();
                $redirectItem->setTitle($row[0]);
                $redirectItem->setSourceHost($row[1]);
                $redirectItem->setSourcePath($row[2]);
                $redirectItem->setTarget($row[3]);
                // date formating
                $startTime = strtotime($row[4]);
                $endDate = strtotime($row[5]);

                $redirectItem->setStartTime((int)$startTime);
                $redirectItem->setEndTime((int)$endDate);
                $redirectItem->setIsRegExp((int)$row[6]);
                $redirectItem->setTargetStatusCode((int)$row[7]);*/
                $row[4] = strtotime($row[4]);
                $row[5] = strtotime($row[5]);
                array_push($rows, $row);
                // verify if the source_path already exists
                if(in_array($row[2], $sourcePathArray, TRUE)){
                    $validImport = false;
                    $this->duplicatedSourcePath = $row[2];
                    break;
                }
                array_push($sourcePathArray, $row[2]);
            }
            else{
                $validImport = false;
                break;
            }
        }

        // save the items if all import are valid
        if($validImport == true){
            if(!is_null($rows) && count($rows) > 0){
                foreach ($rows as $item){

                    //$this->redirectRepository->saveRedirect($item);
                }
                $validImport = true;
            }
            else{
                $validImport = false;
            }
        }
        return $validImport;
    }


    protected function importRedirect($rows)
    {

        $datamap = [];
        foreach ($rows as $key => $item) {
            $datamap['sys_redirects']['NEW_'.$key] = $item;
        }
        $dataHandler = $this->getDatahandler($datamap);
        $dataHandler->process_datamap();
        $success[] = count($datamap[$this->tableName]) . ' événements ont été importés.';
    }

    /**
     * Returns LanguageService
     *
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Returns current BE user
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

}
