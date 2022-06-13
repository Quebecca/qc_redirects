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
    protected array $rowsConstraints = [];

    /**
     * @var array|string[]
     */
    protected array $checkingRules = [
        'inputLink', 'inputDateTime', 'checkboxToggle'
    ];


    /**
     * @var array|string[]
     */
    protected array $errorsTypes = [
        'emptyValue' => false,
        'invalidValue' => false,
        'syntaxError' => false,
        'duplicatedSourcePath' => false,
        'invalidField' => false,
        'readOnlyField' => false
    ];

    /**
     * @var array
     */
    protected array $invalidFields = [];

    /**
     * @var array
     */
    protected array $readOnlyFields = [];

    /**
     * @var array|string[]
     */
    protected array $mandatoryFields = [
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
        if($request == null){
            return null;
        }
        $this->extraFields = GeneralUtility::trimExplode(',',$request->getParsedBody()['extraFields'], true);

        $this->allowedAdditionalFields = $GLOBALS['TCA']['sys_redirect']['columns'];
        $this->invalidFields = array_diff($this->extraFields, array_keys($this->allowedAdditionalFields));

        // todo : save inserted values in the for in case of error

        // check if the field is on ReadOnly
        foreach ($this->extraFields as $field){
            if($this->allowedAdditionalFields[$field]['config']['readOnly']){
                $this->readOnlyFields [] = $field;
            }
        }
        if(!empty($this->readOnlyFields)){
            $this->errorsTypes['readOnlyField'] = true;
            $this->generateAlertMessage(false);
        }

        elseif(!empty($this->invalidFields)){
            $this->errorsTypes['invalidField'] = true;
            $this->generateAlertMessage(false);
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
        $this->renderView($request->getParsedBody());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * This function is used to render View after form submission
     */
    function renderView($requestBody = null){
        $this->view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:qc_redirects/Resources/Private/Templates/Import.html'
        ));
        if($requestBody){
            $this->view->assignMultiple([
                'redirectsList' => $requestBody['redirectsList'],
                'separationCharacter' => $requestBody['separationCharacter'],
                'extraFields' => $requestBody['extraFields']
            ]);
        }
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
            $this->rowsConstraints = array_merge($this->mandatoryFields, $this->extraFields);
            // adding optional fields to be validated
            foreach ($this->rowsConstraints as $fieldName){
                $mappedRow[$fieldName] = $row[$index];
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
            if(count($row) == count($this->rowsConstraints)){
                // verify if the source path,source host, target value is not empty
                if(!$this->verifyMandatoryColumnsExistence($mappedRow)){
                    $validImport = false;
                    break;
                }
                // Map Row to Redirect Entity
                // verify if the source_path is already exists
                if(in_array($mappedRow['source_path'], $sourcePathArray, TRUE)){
                    $validImport = false;
                    $this->duplicatedSourcePath = $mappedRow['source_path'];
                    $this->errorsTypes['duplicatedSourcePath'] = true;
                    break;
                }
                // verify fields values
                $index = 0;
                foreach ($mappedRow as $key => $value){
                    $chckingMethodName = $this->allowedAdditionalFields[$key]['config']['renderType'];
                    if($chckingMethodName != ''){
                        $chckingMethodName .= 'Verify';
                    }
                    if($chckingMethodName != null && in_array($chckingMethodName, $this->checkingRules )){
                        if(!$this->$chckingMethodName($value)){
                            $this->wrongValuekey = $index;
                            $this->errorsTypes['invalidValue'] = true;
                            $validImport = false;
                            break;
                        }
                    }
                    $index++;
                }
                array_push($sourcePathArray,  $mappedRow['source_path']);
                array_push($redirectEntities, $mappedRow);
            }
            else{
                $this->errorsTypes['syntaxError'] = true;
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
    public function verifyMandatoryColumnsExistence(array $row) : bool {
        $i = 0;
        foreach ($this->mandatoryFields as $fieldName){
            if(empty($row[$fieldName])){
                $this->wrongValuekey = $i;
                $this->errorsTypes['emptyValue'] = true;
                return false;
            }
            $i++;
        }
        return true;
    }

    public function inputLinkVerify($value){
      //  return filter_var($value, FILTER_VALIDATE_URL);
        return true;
    }
    public function inputDateTimeVerify($value): bool
    {
        return (bool)strtotime($value);
    }

    public function checkboxToggleVerify($value): bool
    {
        return $value == 'true' || $value == 'false';
    }

    /**
     * This function is used to generate alert message
     * @param bool $success
     */
    public function generateAlertMessage(bool $success){

        if($success){
            $alertMessageHeader = $this->localizationUtility->translate(self::LANG_FILE.'import_success_body');
            $alertMessageBody = $this->localizationUtility->translate(self::LANG_FILE.'success');
            $flashMessageService =  AbstractMessage::OK;
        }
        else{
            $alertMessageBody =  $this->localizationUtility->translate(self::LANG_FILE.'error');
            $flashMessageService =   AbstractMessage::ERROR;
            $alertMessageHeader = "";
            foreach ($this->errorsTypes as $errorType => $value){
                if($value){
                    $alertMessageHeader = $this->localizationUtility->translate(self::LANG_FILE.$errorType);
                    if($this->wrongValuekey != -1){
                        $alertMessageHeader .= " ' ".$this->rowsConstraints[$this->wrongValuekey]." '";
                    }
                    if(!empty($this->readOnlyFields)){
                        $alertMessageHeader .= implode(', ', $this->readOnlyFields);
                    }
                    if(!empty($this->invalidFields)){
                        $alertMessageHeader .= implode(', ', $this->invalidFields);
                    }
                    if($this->duplicatedSourcePath != ''){
                        $alertMessageHeader .= ' "'.  $this->duplicatedSourcePath  .'"'.$this->localizationUtility->translate(self::LANG_FILE.'is_duplicated') ;   ;
                    }
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
