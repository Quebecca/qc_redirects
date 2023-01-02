<?php

namespace Qc\QcRedirects\Domaine\Repository;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Qc\QcRedirects\Controller\ExtendedRedirectModule\v10\DemandExt;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Redirects\Repository\RedirectRepository;

class ExportRedirectsRepository
{


    protected string $tableName = 'sys_redirect';
    protected string $defaultOrderBy = 'createdon';
    protected string $defaultOrderType = 'DESC';
    /**
     * @throws Exception
     */
    public function getRedirectsList(string $orderBy, string $orderType): array
    {
        $queryBuilder = $this->getQueryBuilderForTable($this->tableName);

        // 	createdby
        //target :::::: t3://page?uid=13877

        $orderBy = $this->checkIfColumnExistsInTable($orderBy) ? $orderBy : $this->defaultOrderBy;
        $orderType = in_array($orderType, ['ASC', 'DESC']) ? $orderType : $this->defaultOrderType;

        $data = $queryBuilder->select('uid', 'createdon','updatedon', 'target', 'title', 'source_path')
            ->from($this->tableName)
            ->orderBy($orderBy,$orderType)
            ->execute()
            ->fetchAllAssociative();

        $csvData = [];
        foreach ($data as $item){
            if(str_contains($item['target'], 't3://page?uid=')){
                $pageUid =intval(str_replace('t3://page?uid=', '' ,$item['target']));
            }
            else if(!str_contains($item['target'], 'http://') && !str_contains($item['target'], 'https://')){
                // it's a slug
                $pageUid = $this->findPageUidBySlug($item['target']);
            }

            // Gérer les cas où on apas t3://....
            $associatedData = $this->getAssociatedGroupNameAndPageSlug(intval($pageUid));
            $item['beGroup'] = $associatedData['groupName'];
            $item['slug'] = $associatedData['slug'];
            $item['createdon'] = date('d/m/y',  $item['createdon']);
            $item['updatedon'] = date('d/m/y',  $item['updatedon']);
            $csvData[] = $item;
            $pageUid = null;
        }
        return $csvData;
    }

    protected function getAssociatedGroupNameAndPageSlug(int $pageUid): array
    {

        $data = BackendUtility::getRecord('pages', $pageUid, 'perms_groupid, slug');
        $pageSlug = $data['slug'];
        $beGroupName = BackendUtility::getRecord('be_groups',intval($data['perms_groupid']),'title')['title'];

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
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName);
        /*$queryBuilder
            ->getRestrictions()
            ->removeAll();*/
        return $queryBuilder;
    }


    /**
     * @throws Exception
     */
    protected function findPageUidBySlug(string $slug){
        $queryBuilder = $this->getQueryBuilderForTable('pages');
        $results = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->like('slug', $queryBuilder->createNamedParameter($slug)),
            )
            ->execute()
            ->fetchAssociative();

        if($results !== false)
            return $results['uid'];
        return null;
    }


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