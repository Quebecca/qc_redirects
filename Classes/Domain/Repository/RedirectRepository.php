<?php
namespace QcRedirects\Domain\Repository;
use QcRedirects\Domain\Model\Redirect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RedirectRepository
{
    public function saveRedirect(Redirect  $redirect){
        // Save the record in the DB
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_redirect');
        $affectedRows = $queryBuilder
            ->insert('sys_redirect')
            ->values([
                'title' => $redirect->getTitle(),
                'source_host' => $redirect->getSourceHost(),
                'source_path' => $redirect->getSourcePath(),
                'target' => $redirect->getTarget(),
                'target_statuscode' => $redirect->getTargetStatusCode(),
                'starttime' => $redirect->getStartTime(),
                'endtime' => $redirect->getEndTime(),
                'is_regexp' => $redirect->getIsRegExp(),
            ])
            ->execute();
    }

    public function getSourcePaths() : array {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_redirect');
        $sourcePathArray = [];
        $statement = $queryBuilder
            ->select('source_path')
            ->from('sys_redirect')
            ->execute();
        while ($row = $statement->fetch()) {
            array_push($sourcePathArray, $row['source_path']);
        }
        return $sourcePathArray;
    }

}