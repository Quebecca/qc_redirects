<?php

namespace Qc\QcRedirects\Domaine\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExportRedirectsRepository
{

    /**
     * @var string
     */
    protected string $tableName = 'sys_redirect';

    /**
     * @var string
     */
    protected string $defaultOrderBy = 'createdon';

    /**
     * @var string
     */
    protected string $defaultOrderType = 'DESC';

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function getRedirectsList(string $orderBy, string $orderType): array
    {
        $queryBuilder = $this->getQueryBuilderForTable($this->tableName);

        $orderBy = $this->checkIfColumnExistsInTable($orderBy) ? $orderBy : $this->defaultOrderBy;
        $orderType = in_array($orderType, ['ASC', 'DESC']) ? $orderType : $this->defaultOrderType;

        $data = $queryBuilder->select('uid', 'createdon','updatedon', 'title', 'source_path', 'target')
            ->from($this->tableName)
            ->orderBy($orderBy,$orderType)
            ->execute()
            ->fetchAllAssociative();

        $csvData = [];
        foreach ($data as $item){
            $pageUid = $this->getPageUidFromTarget($item['target']);
            // Gérer les cas où on apas t3://....
            $associatedData = $this->getAssociatedGroupNameAndPageSlug(intval($pageUid));
            $item['beGroup'] = $associatedData['groupName'];
            $item['slug'] = $associatedData['slug'];
            $item['createdon'] = date('d/m/y',  $item['createdon']);
            $item['updatedon'] = date('d/m/y',  $item['updatedon']);
            $csvData[] = $item;
        }
        return $csvData;
    }

    /**
     * @param $target
     * @return int|null
     * @throws DBALException
     * @throws Exception
     */
    public function getPageUidFromTarget($target): ?int
    {
        $pageUid = null;
        if(is_numeric($target) == true){
            $pageUid = $target;
        }
        elseif (str_contains($target, 't3://page?uid=')){
            $pageUid =intval(str_replace('t3://page?uid=', '' ,$target));
        }
        // it's a slug
        elseif (!str_contains($target, 'http://') && !str_contains($target, 'https://')){
            $pageUid = $this->findPageUidBySlug($target);
        }

        if($pageUid !== null){
            // Check if the uid is present in the DB
            if(BackendUtility::getRecord('pages', intval($pageUid)) !== null){
                return intval($pageUid);
            }
        }
        return null;
    }

    /**
     * @param int $pageUid
     * @return array
     */
    protected function getAssociatedGroupNameAndPageSlug(int $pageUid): array
    {
        $data = BackendUtility::getRecord('pages', $pageUid, 'perms_groupid, slug');
        $pageSlug = $data['slug'] ?? '';
        $beGroupName = BackendUtility::getRecord(
            'be_groups'
            ,intval($data['perms_groupid'] ?? -1)
            ,'title'
            )
            ['title']
            ?? '';
        return [
            'slug' => $pageSlug,
            'groupName' => $beGroupName
        ] ;
    }

    /**
     * Generate query builders
     * @param string $tableName
     * @return QueryBuilder
     */
    protected function getQueryBuilderForTable(string $tableName): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName);
    }


    /**
     * This function is used to find pageUid by it's slug
     * @param string $slug
     * @return mixed|null
     * @throws DBALException
     * @throws Exception
     */
    protected function findPageUidBySlug(string $slug)
    {
        $queryBuilder = $this->getQueryBuilderForTable('pages');
        $results = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->like('slug', $queryBuilder->createNamedParameter($slug)),
            )
            ->execute()
            ->fetchAssociative();

        if($results !== false){
            return $results['uid'];
        }
        return null;
    }


    /**
     * This function is used to check if the column is present in the DB table
     * @param $columnName
     * @return bool
     */
    protected function checkIfColumnExistsInTable($columnName) : bool {
        return in_array(
            $columnName,
            array_keys(
                $this->getSchemaManager($this->tableName)->listTableColumns($this->tableName)
            )
        );
    }

    /**
     * This function is uesd to return the SchemaManager
     * @param string $tableName
     * @return AbstractSchemaManager|null
     */
    public function getSchemaManager(string $tableName): ?AbstractSchemaManager
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($tableName)
            ->getSchemaManager();
    }
}