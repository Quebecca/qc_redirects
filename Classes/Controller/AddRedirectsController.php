<?php
declare(strict_types=1);
/***
 *
 * This file is part of Qc Redirects project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2022 <techno@quebec.ca>
 *
 ***/

namespace QcRedirects\Controller;

use Doctrine\DBAL\Driver\Exception;
use LST\BackendModule\Controller\BackendModuleActionController;
use Psr\Http\Message\ServerRequestInterface;
use QcRedirects\Domaine\Repository\ImportRedirectsRepository;
use QcRedirects\Mapper\RedirectEntityMapper;
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

    const NUMBER_OF_FIELDS = 4;
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

    /**
     * @var RedirectEntityMapper
     */
    protected RedirectEntityMapper $redirectMapper;

    /**
     * @var array|string[]
     */
    protected array $separatedChars = [
        'tabulation' => "\t",
        'pipe' => '|',
        'semicolon' => ';',
        'colon' => ':',
        'comma' => ',',
    ];

    /**
     * @var int
     */
    protected int $wrongValuekey = -1;

    /**
     * @var array|string[]
     */
    protected array $rowsConstraints = [
        0 => 'source_host',
        1 => 'source_path',
        2 => 'target',
        3 => 'is_regexp'
    ];

    /**
     * @var array|string[]
     */
    protected array $allowedAdditionalFields  = [
        'title',
        'startTime',
        'endTime',
        'statusCode'
    ];

    /**
     * @var string
     */
    protected string $selectedSeparatedChar = '';

    protected array $extraFields = [];

    /**
     * @var string
     */
    protected string $duplicatedSourcePath = '';

    /**
     * @var ImportRedirectsRepository
     */
    protected ImportRedirectsRepository  $importRedirectsRepository;

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @param ModuleTemplate|null $moduleTemplate
     * @param LocalizationUtility|null $localizationUtility
     * @param StandaloneView|null $view
     */
    public function __construct(
        ModuleTemplate $moduleTemplate = null,
        LocalizationUtility $localizationUtility = null,
        StandaloneView $view = null
    )
    {
        $this->localizationUtility = $localizationUtility ?? GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->moduleTemplate = $moduleTemplate ?? GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->moduleTemplate->getPageRenderer()->addCssFile('EXT:qc_redirects/Resources/Public/Css/qc_redirects.css');
        $this->view = $view ?? GeneralUtility::makeInstance(StandaloneView::class);
        $this->redirectMapper =  GeneralUtility::makeInstance(RedirectEntityMapper::class);
        $this->importRedirectsRepository = GeneralUtility::makeInstance(ImportRedirectsRepository::class);
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
        debug($GLOBALS['TCA']['sys_redirect']['columns']);
        if($request == null){
            return null;
        }
        $this->extraFields = GeneralUtility::trimExplode(',',$request->getParsedBody()['extraFields'], true);
        $this->allowedAdditionalFields = array_keys($GLOBALS['TCA']['sys_redirect']['columns']);
    //    debug($this->allowedAdditionalFields);
        if(!empty(array_diff($this->extraFields, $this->allowedAdditionalFields))){
            $this->generateAlertMessage(false, true);
        }
        else{
            $redirectsList = $request->getParsedBody()['redirectsList'];
            $this->selectedSeparatedChar = $request->getParsedBody()['separationCharacter'];
            if(!is_null($redirectsList)){
                // convert data to array
                $redirectsListArray = explode("\r\n", $redirectsList);
                $created = $this->processRedirects($redirectsListArray);
                // alert message
                $this->generateAlertMessage($created);
            }
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
     * This function will be used to get the imported data header, by combining mandatory fields with additional fields
     * @return array
     */
    protected function getImportedDataHeader() : array{
        return [];
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
        $redirectEntities = [];
        // get the source_paths from the DB
        $sourcePathArray = $this->importRedirectsRepository->getSourcePaths();
        // precessing each line in the array
        foreach ($redirectListArray as $item){
            // separate the row columns
            $row = explode($this->separatedChars[$this->selectedSeparatedChar],$item);
            $mappedRow = [];
            $index = 0;
            $importedDataHeader = array_merge($this->rowsConstraints, $this->extraFields);
            foreach ($importedDataHeader as $fieldName){
                if($fieldName == 'is_regexp')
                    $mappedRow[$fieldName] = strtolower($row[$index]) == 'true' ? 1 : (strtolower($row[$index]) == 'false' ? 0 : false);
                else{
                    if($fieldName == 'starttime' || $fieldName == 'endtime')
                        $mappedRow[$fieldName] = strtotime($row[$index]);
                    else{
                        $mappedRow[$fieldName] = $row[$index];
                    }
                }

                 $index++;
            }

            // remove white spacing
            if($this->selectedSeparatedChar !== "tabulation"){
                $row[0] = preg_replace('/\s+/', '', $row[0]);
                $row[1] = preg_replace('/\s+/', '', $row[1]);
            }
            // empty line
            if(count($row) == 1 && $row[0] == ''){
                continue;
            }

            // make sure that we have all important fields
            // todo : limit number of imported fields
            if(count($row) >= self::NUMBER_OF_FIELDS){
                //$redirectEntity = $this->redirectMapper->rowToRedirectEntity($associatifRow);
                // verify if the source path,source host, target value is not empty

                if($this->verifyColumnsValues([
                    $mappedRow['source_host'],
                    $mappedRow['source_path'],
                    $mappedRow['target'],
                    $mappedRow['is_regexp'],
                ])){
                    $validImport = false;
                    break;
                }
                // Map Row to Redirect Entity
                // verify if the source_path is already exists
                if(in_array($mappedRow['source_path'], $sourcePathArray, TRUE)){
                    $validImport = false;
                    $this->duplicatedSourcePath = $mappedRow['source_path'];
                    break;
                }
                array_push($sourcePathArray,  $mappedRow['source_path']);
                array_push($redirectEntities, $mappedRow);
            }
            else{
                $validImport = false;
                break;
            }
        }
        // save the items if all import are valid
        if($validImport && !is_null($redirectEntities) && count($redirectEntities) > 0){
            $this->importRedirectsRepository->saveRedirects($redirectEntities);
            return true;
        }
        return false;
    }


    /**
     * This function is used to verify the values of the columns
     * @param array $row
     * @return bool
     */
    public function verifyColumnsValues(array $row) : bool {
        $i = 0;
        $invalidValue = false;
        foreach ($row as $item){
            if(empty($item)){
                $this->wrongValuekey = $i;
                $invalidValue = true;
                break;
            }
            $i++;
        }
        // verify the regular expression value
        if($row[3] == false){
            $this->wrongValuekey = 3;
            $invalidValue = true;
        }
        return $invalidValue;
    }

    /**
     * This function is used to generate alert message
     * @param bool $success
     */
    public function generateAlertMessage(bool $success, bool $wrongAdditionalFileds = false){
        if($wrongAdditionalFileds){
            $alertMessageHeader = "Error";
            $alertMessageBody = "Field(s) are not available";
            $flashMessageService =  AbstractMessage::ERROR;
        }
        else{
            if($success){
                $alertMessageHeader = $this->localizationUtility->translate(self::LANG_FILE.'import_success_body');
                $alertMessageBody = $this->localizationUtility->translate(self::LANG_FILE.'success');
                $flashMessageService =  AbstractMessage::OK;
            }
            else{

                $alertMessageBody =  $this->localizationUtility->translate(self::LANG_FILE.'error');
                $flashMessageService =   AbstractMessage::ERROR;
                if($this->wrongValuekey !== -1){
                    $alertMessageHeader = $this->localizationUtility->translate(self::LANG_FILE.'import_invalidValue'). " ' ". $this->localizationUtility->translate(self::LANG_FILE.$this->rowsConstraints[$this->wrongValuekey])." '";
                }
                elseif (!empty($this->duplicatedSourcePath)){
                    $alertMessageHeader = $this->localizationUtility->translate(self::LANG_FILE.'import_error_duplicated')
                        . ' "'. $this->duplicatedSourcePath .'"'.$this->localizationUtility->translate(self::LANG_FILE.'is_duplicated') ;
                }
                else{
                    $alertMessageHeader = $this->localizationUtility->translate(self::LANG_FILE.'import_error_syntax');
                }
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
