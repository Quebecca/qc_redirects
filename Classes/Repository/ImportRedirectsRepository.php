<?php
namespace QcRedirects\Repository;

use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImportRedirectsRepository
{

    protected $queryBuilder;

    protected DataHandler $dataHandler;

    protected string $table = 'sys_redirect';

    public function __construct()
    {
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
    }

    /**
     * This function use the Datahandler for store records in sys_redirects table
     * @param $rows
     */
    public function saveRedirects($rows)
    {
        $data =[];
        foreach ($rows as $key => $row) {
            $data[$this->table]['NEW_'.$key] = $row;
        }
        $this->dataHandler->start($data, []);
        $this->dataHandler->process_datamap();
    }

    /**
     * This function returns the columns source_path from the stored records, for comparing and prevent saving duplicated values for source_path
     * @return array
     * @throws Exception
     */
    public function getSourcePaths() : array {
        $sourcePathArray = [];
        $this->queryBuilder
            ->getRestrictions()
            ->removeAll();
        $statement = $this->queryBuilder
            ->select('source_path')
            ->from($this->table)
            ->execute();
        while ($row = $statement->fetchAssociative()) {
            array_push($sourcePathArray, $row['source_path']);
        }
        return $sourcePathArray;
    }
}
