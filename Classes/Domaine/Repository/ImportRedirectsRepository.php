<?php
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

namespace Qc\QcRedirects\Domaine\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImportRedirectsRepository
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var DataHandler
     */
    protected DataHandler $dataHandler;

    /**
     * @var string
     */
    protected string $table = 'sys_redirect';

    public function __construct()
    {
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
    }

    /**
     * This function use the Datahandler for store records in sys_redirects table
     * @param $redirectsEntities
     */
    public function saveRedirects($redirectsEntities)
    {
        $data =[];
        foreach ($redirectsEntities as $key => $redirectsEntity) {
            $redirectsEntity['pid'] = '0';
            $data[$this->table]['NEW_'.$key] = $redirectsEntity;
        }
        $this->dataHandler->start($data, []);
        $this->dataHandler->process_datamap();
    }

    /**
     * This function returns the columns source_path from the stored records, for comparing and prevent saving duplicated values for source_path
     * @return array
     * @throws Exception|DBALException
     */
    public function getSourcePaths() : array {
        $sourcePathArray = [];

        $this->queryBuilder
            ->getRestrictions()
            ->removeByType(HiddenRestriction::class);

        $statement = $this->queryBuilder
            ->select('source_path')->from($this->table)->executeQuery();
        while ($row = $statement->fetchAssociative()) {
            $sourcePathArray[] = $row['source_path'];
        }
        return $sourcePathArray;
    }
}
