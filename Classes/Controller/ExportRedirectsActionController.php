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

namespace Qc\QcRedirects\Controller;

use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcRedirects\Domaine\Repository\ExportRedirectsRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ExportRedirectsActionController
{

    /**
     * @var string
     */
    const LANG_FILE = "LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:";

    /**
     * @var ExportRedirectsRepository
     */
    protected ExportRedirectsRepository $exportRedirectsRepository;

    /**
     * @var string
     */
    protected string $enclosure ;

    /**
     * @var string
     */
    protected string $separator;

    /**
     * @var CharsetConverter
     */
    protected CharsetConverter $charsetConverter;

    /**
     * @var LocalizationUtility
     */
    protected LocalizationUtility $localizationUtility;

    /**
     * @var array
     */
    protected array $userTS;

    /**
     * @var string
     */
    protected string $orderType = '';

    /**
     * @var string
     */
    protected string $orderBy = '';

    /**
     * @var array|string[]
     */
    protected array $csvHeader = [
        'uid',
        'createdon',
        'updatedon',
        'title',
        'source_path',
        'target',
        'beGroup',
        'slug'
    ];

    public function __construct()
    {
        $this->charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);
        $this->exportRedirectsRepository = GeneralUtility::makeInstance(ExportRedirectsRepository::class);
        $this->localizationUtility = GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->initializeTsConfig();
        $this->enclosure =$this->userTS['enclosure'] ?? '"';
        $this->separator =$this->userTS['separator'] ?? ';';
        $this->orderType =$this->userTS['orderType'] ?? 'DESC';
        $this->orderBy =$this->userTS['orderBy'] ?? 'createdon';
        //CSV HEADERS Using Translate File
        $this->generateCsvHeaderArray($this->csvHeader);
    }


    /**
     * This Action is to export Redirects list as a CSV Files
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function exportRedirectsListAction(ServerRequestInterface $request): ResponseInterface
    {
        //Initialize Response and create Name of Our FIle CSV
        $filename = $this->localizationUtility->translate(self::LANG_FILE .'export_redirects_list') . date('Y-m-d_H-i').'.csv';

        $response = new Response('php://output', 200,
            [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Description' => 'File transfer',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );

        /**Getting redirects data*/
        $data = $this->exportRedirectsRepository->getRedirectsList($this->orderBy,$this->orderType);
        //Open File Based on Function Php To start Write inside the file CSV
        $fp = fopen('php://output', 'wb');
        // UTF-8 encoding issu
        fwrite($fp, "\xEF\xBB\xBF");

        fputcsv($fp, $this->csvHeader, $this->separator, $this->enclosure);

        foreach ($data as $item) {
            //Write Inside Our CSV File
            fputcsv($fp, $item, $this->separator, $this->enclosure);
        }

        fclose($fp);
        return $response;
    }

    /**
     * This Function to Generate an array for Header CSV Based on Language file get as parameter and array of key of language file "LLL:EXT:qc_info_rights/Resources/Private/Language/Module/locallang.xlf"
     *
     * @param array $itemsArray
     *
     */
    protected function generateCsvHeaderArray(array $itemsArray)
    {
        for ($i = 0; $i < count($itemsArray); $i++) {
            $this->csvHeader[$i] = $this->localizationUtility->translate(self::LANG_FILE .'csvHeader.'. $itemsArray[$i]);
        }
    }

    protected function initializeTsConfig(){
        /*Initialize the TsConfing mod of the current Backend user */
        $this->userTS = $this->getBackendUser()->getTSConfig()['mod.']['qcRedirects.']['csvExport.'];
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}