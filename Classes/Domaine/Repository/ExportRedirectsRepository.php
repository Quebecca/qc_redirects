<?php

namespace Qc\QcRedirects\Domaine\Repository;

use Qc\QcRedirects\Controller\ExtendedRedirectModule\v10\DemandExt;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Redirects\Repository\RedirectRepository;

class ExportRedirectsRepository
{


    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getRedirectsList(){
        $queryBuilder = $this->getQueryBuilderForTable('sys_redirect');

        $csvData = [];
        // 	createdby
        //target :::::: t3://page?uid=13877

        $data = $queryBuilder->select('uid', 'createdon','updatedon', 'target', 'title', 'source_path')
            ->from('sys_redirect')
            ->execute()
            ->fetchAllAssociative();

        $csvData = [];
        foreach ($data as $item){
            $pageUid =intval(str_replace('t3://page?uid=', '' ,$item['target']));

            // Gérer les cas où on apas t3://....
            $associatedData = $this->getAssociatedGroupNameAndPageSlug(intval($pageUid));
            $item['beGroup'] = $associatedData['groupName'];
            $item['slug'] = $associatedData['slug'];
            $csvData[] = $item;
        }
        return $csvData;
    }

    protected function getAssociatedGroupNameAndPageSlug(int $pageUid){

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
        return  GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName);
        /*$queryBuilder
            ->getRestrictions()
            ->removeAll();*/
    }
}