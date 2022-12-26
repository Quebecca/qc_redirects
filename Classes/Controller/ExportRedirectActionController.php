<?php

namespace Qc\QcRedirects\Controller;

use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qc\QcRedirects\Domaine\Repository\ExportRedirectsRepository;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ExportRedirectActionController
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
    protected $quote;

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * @var CharsetConverter
     */
    protected $charsetConverter;

    /**
     * @var LocalizationUtility
     */
    protected $localizationUtility;


    public function __construct()
    {
        $this->charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);
        $this->exportRedirectsRepository =  GeneralUtility::makeInstance(ExportRedirectsRepository::class);
        $this->localizationUtility ??= GeneralUtility::makeInstance(LocalizationUtility::class);

        //Render configuration from ext_conf_template file for quote and delimter
        /* $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
         $configuration = $extensionConfiguration->get('qc_redirects');
         if (is_array($configuration)) {
             $this->quote = $configuration['quote'] ?? '"';

             $this->delimiter = $configuration['delimiter'] ?? ',';

             }*/
        $this->quote = '"';
        $this->delimiter = ';';
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
        $filename = 'Redirects-list-Export-' . date('Y-m-d_H-i').'.csv';

        $response = new Response('php://output', 200,
            [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Description' => 'File transfer',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );


        /**Getting redirects data*/
        $data = $this->exportRedirectsRepository->getRedirectsList();

        // Build header array for csv headers
        $headerArray = [];
        foreach (array_keys($data[0]) as $headerName){
            $headerArray[] = 'csvHeader.'.$headerName;
        }
        //CSV HEADERS Using Translate File and respecting UTF-8 Charset for Special Char
        $headerCsv = $this->generateCsvHeaderArray($headerArray);

        //Open File Based on Function Php To start Write inside the file CSV
        $fp = fopen('php://output', 'wb');

        fputcsv($fp, $headerCsv, $this->delimiter, $this->quote);

        foreach ($data as $item) {
            //Write Inside Our CSV File
            fputcsv($fp, $item, $this->delimiter, $this->quote);
        }
        fclose($fp);
        return $response;
    }

    /**
     * This Function to Generate an array for Header CSV Based on Language file get as parameter and array of key of language file "LLL:EXT:qc_info_rights/Resources/Private/Language/Module/locallang.xlf"
     *
     * @param array $itemsArray
     *
     * @return array
     */
    protected function generateCsvHeaderArray(array $itemsArray): array
    {
        $headerCsv = [];
        for ($i = 0; $i < count($itemsArray); $i++) {
            $headerCsv[] = $this->charsetConverter->conv($this->localizationUtility->translate(self::LANG_FILE . $itemsArray[$i]), 'utf-8', 'iso-8859-15');
        }
        return $headerCsv;
    }
}