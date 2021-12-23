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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;


class AddRedirectsController  extends BackendModuleActionController
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * ModuleTemplate object
     *
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /*
     * @var RedirectRepository
     */
    private $redirectRepository;

    protected  $duplicatedSourcePath = '';

    public function __construct(ModuleTemplate $moduleTemplate = null, RedirectRepository $redirectRepository = null)
    {
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
        $this->request = $request;
        // rendering after form submit
        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:qc_redirects/Resources/Private/Templates/Import.html'
        ));
        if(!is_null($request)){
            $redirectsList = $request->getParsedBody()['redirectsList'];
            $character = $this->request->getParsedBody()['indentationCharacter'];
        }

        if(!is_null($redirectsList)){
            // convert data to array
            $redirectsListArray = explode("\r\n", $redirectsList);
            $created = $this->createRedirectsList($redirectsListArray);
            // alert message
            $message = '';
            if($created == true){
                $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class,
                    $this->getLanguageService()->sL('LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:import_success_body'),
                    $this->getLanguageService()->sL('LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:success'),
                    \TYPO3\CMS\Core\Messaging\FlashMessage::OK,
                );
                $flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
                $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                $messageQueue->addMessage($message);
            }
            else {
                if($this->duplicatedSourcePath == ''){
                    $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class,
                        $this->getLanguageService()->sL('LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:import_error_syntax'),
                        $this->getLanguageService()->sL('LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:error'),
                        \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                    );
                }
                else{
                    $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class,
                        $this->getLanguageService()->sL('LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:import_error_duplicated')
                        . ' "'. $this->duplicatedSourcePath .'"'. $this->getLanguageService()->sL('LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:is_duplicated') ,
                        $this->getLanguageService()->sL('LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:error'), // [optional] the header
                        \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR,
                    );
                }

                $flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
                $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                $messageQueue->addMessage($message);
            }
        }
        $view->assign('character', $character);
        $this->moduleTemplate->setContent($view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * Persist imported list of redirects in DB
     *
     * @return bool TRUE if the was list was successfully stored in the database
     */
    protected function createRedirectsList(array $redirectListArray): bool
    {
        $validImport = true;
        $importedRows = [];
        // get the source_paths from the DB
        $sourcePathArray = $this->redirectRepository->getSourcePaths();
        // precessing each line in the array
        foreach ($redirectListArray as $item){
            // separate the row columns
            $row = explode($this->getSeparationChar(),$item);
            // empty line
            if(count($row) == 1 && $row[0] == ''){
                continue;
            }
            // verify if all columnus are valide
            // we have to make sure that we have all important fields
            // make sure that all the fields are valid
            if(count($row) == 8){
                // Mapping item to Demand Redirect Reposiotry
                // title, source host, source path, target, start time, end time, is regular experssion, status code
                $redirectItem = new Redirect();
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
                $redirectItem->setTargetStatusCode((int)$row[7]);
                array_push($importedRows, $redirectItem);
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
            if(!is_null($importedRows) && count($importedRows) > 0){
                foreach ($importedRows as $item){
                    $this->redirectRepository->saveRedirect($item);
                }
                $validImport = true;
            }
            else{
                $validImport = false;
            }
        }
        return $validImport;
    }


    /**
     *
     * @return   string      the separation character
     */
    private function getSeparationChar(): string
    {
        $character = $this->request->getParsedBody()['separationCharacter'];
        switch ($character) {
            case 'tab':
                $character = "\t";
                break;
            case 'pipe':
                $character = '|';
                break;
            case 'semicolon':
                $character = ';';
                break;
            case 'colon':
                $character = ':';
                break;
            case 'comma':
            default:
                $character = ',';
                break;
        }

        return $character;
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
