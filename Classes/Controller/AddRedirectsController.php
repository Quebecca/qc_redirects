<?php
declare(strict_types=1);
namespace QcRedirects\Controller;

use LST\BackendModule\Controller\BackendModuleActionController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\Response;
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

    protected $queryBuilder;

    /**
     * @var string
     */
    protected string $table = 'sys_redirect';

    protected array $separatedChars = [
        'tabulation' => "\t",
        'pipe' => '|',
        'semicolon' => ';',
        'colon' => ':',
        'comma' => ',',
    ];

    protected DataHandler $dataHandler;

    protected string $selectedSeparatedChar = '';

    protected string $duplicatedSourcePath = '';

    protected $view;

    public function __construct(
        ModuleTemplate $moduleTemplate = null,
        LocalizationUtility $localizationUtility = null,
        StandaloneView $view = null
    )
    {
        $this->localizationUtility = $localizationUtility ?? GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->moduleTemplate = $moduleTemplate ?? GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
        $this->view = $view ?? GeneralUtility::makeInstance(StandaloneView::class);
    }
    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     * @return void
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
     * @param ServerRequestInterface|null $request
     * @return HtmlResponse|null
     */
    public function importAction(ServerRequestInterface $request = null)
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

    function renderView(){
        $this->view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:qc_redirects/Resources/Private/Templates/Import.html'
        ));
        $this->view->assign('separatedChars', $this->separatedChars);
        $this->moduleTemplate->setContent($this->view->render());
    }

    /**
     * @return bool TRUE if the list was successfully stored in the database
     */
    protected function processRedirects(array $redirectListArray): bool
    {
        $validImport = true;
        $rows = [];
        // get the source_paths from the DB
        $sourcePathArray = $this->getSourcePaths();
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
                array_push($sourcePathArray, $row[2]);
            }
            else{
                $validImport = false;
                break;
            }
        }
        // save the items if all import are valid
        if($validImport && !is_null($rows) && count($rows) > 0){
            $this->saveRedirects($rows);
            return true;
        }
        return false;
    }

    /**
     * @param $rows
     */
    protected function saveRedirects($rows)
    {
        $data =[];
        foreach ($rows as $key => $row) {
            $data[$this->table]['NEW_'.$key] = $row;
        }
        $this->dataHandler->start($data, []);
        $this->dataHandler->process_datamap();
    }

    /**
     * @return array
     */
    public function getSourcePaths() : array {
        $sourcePathArray = [];
        $statement = $this->queryBuilder
            ->select('source_path')
            ->from($this->table)
            ->execute();
        while ($row = $statement->fetch()) {
            array_push($sourcePathArray, $row['source_path']);
        }
        return $sourcePathArray;
    }


    /**
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
