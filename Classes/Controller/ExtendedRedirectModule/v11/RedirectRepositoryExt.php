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
namespace Qc\QcRedirects\Controller\ExtendedRedirectModule\v11;

use Qc\QcRedirects\Controller\ExtendedRedirectModule\v11\DemandExt;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Redirects\Repository\Demand;
use TYPO3\CMS\Redirects\Repository\RedirectRepository;

class RedirectRepositoryExt extends RedirectRepository
{
    /**
     * @var DemandExt
     */
    protected $demand;

    /**
     * @var string
     */
    protected string $orderBy = 'createdon';

    /**
     * @var string
     */
    protected string $orderType = 'ASC';

    /**
     * @param string $orderBy
     */
    public function setOrderBy(string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @param string $orderType
     */
    public function setOrderType(string $orderType): void
    {
        $this->orderType = $orderType;
    }

    /**
     * @param Demand $demand
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findRedirectsByDemand(Demand $demand): array
    {
       return
           $this->getQueryBuilderForDemand($demand)
            ->setMaxResults($demand->getLimit())
            ->setFirstResult($demand->getOffset())
            ->orderBy( $this->orderBy,$this->orderType)
            ->execute()
            ->fetchAllAssociative();
    }


    /**
     * @param Demand $demand
     * @return QueryBuilder
     */
    protected function getQueryBuilderForDemand(Demand $demand): QueryBuilder
    {
        $queryBuilder = parent::getQueryBuilderForDemand($demand);
        $constraints = '';
        if ($demand->hasTitle()) {
            $escapedLikeString = '%' . $queryBuilder->escapeLikeWildcards($demand->getTitle()) . '%';
            $constraints = $queryBuilder->expr()->like(
                'title',
                $queryBuilder->createNamedParameter($escapedLikeString, \PDO::PARAM_STR)
            );
        }
        return $queryBuilder->andWhere($constraints);
    }


}