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
namespace QcRedirects\Controller\ExtendedRedirectModule;

use TYPO3\CMS\Redirects\Repository\RedirectRepository;

class RedirectRepositoryExt extends RedirectRepository
{
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
     */
    public function findRedirectsByDemand(): array
    {
        return $this->getQueryBuilderForDemand()
            ->setMaxResults($this->demand->getLimit())
            ->setFirstResult($this->demand->getOffset())
            //->orderBy($this->orderBy, $this->orderType)
            ->orderBy($this->orderBy, $this->orderType)
            ->execute()
            ->fetchAll();
    }

}