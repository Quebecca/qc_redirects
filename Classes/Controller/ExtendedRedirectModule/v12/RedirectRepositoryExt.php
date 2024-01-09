<?php

namespace Qc\QcRedirects\Controller\ExtendedRedirectModule\v12;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Redirects\Repository\Demand;
use TYPO3\CMS\Redirects\Repository\RedirectRepository;

class RedirectRepositoryExt extends RedirectRepository
{

    /**
     * Prepares the QueryBuilder with Constraints from the Demand
     */
    protected function getQueryBuilderForDemand(Demand $demand, bool $createCountQuery = false): QueryBuilder
    {
        $constraints = "";
        $queryBuilder = parent::getQueryBuilderForDemand($demand, $createCountQuery);
        if ($demand instanceof DemandExt && $demand->hasTitle()) {
            $escapedLikeString = '%' . $queryBuilder->escapeLikeWildcards($demand->getTitle()) . '%';
            $constraints = $queryBuilder->expr()->like(
                'title',
                $queryBuilder->createNamedParameter($escapedLikeString)
            );
        }

        return $queryBuilder->andWhere($constraints);
    }
}
