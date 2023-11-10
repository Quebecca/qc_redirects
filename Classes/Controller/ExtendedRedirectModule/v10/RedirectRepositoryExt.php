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



namespace Qc\QcRedirects\Controller\ExtendedRedirectModule\v10;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
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
    protected string $orderBy = '';

    /**
     * @var string
     */
    protected string $orderType = '';

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
     * Used within the backend module, which also includes the hidden records, but never deleted records.
     * This core function is overloaded to add sort operation
     *
     * @return array
     * @throws Exception
     */
/*    public function findRedirectsByDemand(): array
    {
        return $this->getQueryBuilderForDemand()
            ->setMaxResults($this->demand->getLimit())
            ->setFirstResult($this->demand->getOffset())
            ->orderBy($this->orderBy, $this->orderType)
            ->execute()
            ->fetchAll();
    }


    /**
     * Prepares the QueryBuilder with Constraints from the Demand
     *
     * @return QueryBuilder
     */
/*    protected function getQueryBuilderForDemand(): QueryBuilder
    {
        $queryBuilder = parent::getQueryBuilderForDemand();
        $constraints = '';
        if ($this->demand->hasTitle()) {
            $escapedLikeString = '%' . $queryBuilder->escapeLikeWildcards($this->demand->getTitle()) . '%';
            $constraints = $queryBuilder->expr()->like(
                'title',
                $queryBuilder->createNamedParameter($escapedLikeString, \PDO::PARAM_STR)
            );
        }
        return $queryBuilder->andWhere($constraints);
    }*/
}
