<?php

declare(strict_types=1);

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

namespace QcRedirects\Controller\ExtendedRedirectModule\v11;

use QcRedirects\Util\Arrayable;
use TYPO3\CMS\Redirects\Repository\Demand;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Demand Object for filtering redirects in the backend module
 * @internal
 */
class DemandExt extends Demand implements Arrayable
{
    /**
     * @var string
     */
    protected string $title;

    /**
     * @var string
     */
    protected string $orderBy;

    /**
     * @var string
     */
    protected string $orderType;


    public function __construct(
        int $page = 1,
        string $orderField = self::DEFAULT_ORDER_FIELD,
        string $orderDirection = self::ORDER_ASCENDING,
        array $sourceHosts = [],
        string $sourcePath = '',
        string $target = '',
        array $statusCodes = [],
        int $maxHits = 0,
        \DateTimeInterface $olderThan = null,
        string $title = '',
        string $orderBy = '',
        string $orderType = ''
    )
    {
        parent::__construct($page,$orderField,$orderDirection,$sourceHosts,$sourcePath,$target,$statusCodes,$maxHits,$olderThan);
        $this->title = $title;
        $this->orderType = $orderType;
        $this->orderBy = $orderBy;
    }

    /**
     * Creates a Demand object from the current request.
     *
     * @param ServerRequestInterface $request
     * @return DemandExt
     */
    public static function createFromRequest(ServerRequestInterface $request): DemandExt
    {
        $page = (int)($request->getQueryParams()['page'] ?? $request->getParsedBody()['page'] ?? 1);
        $demand = $request->getQueryParams()['demand'] ?? $request->getParsedBody()['demand'];

        if (empty($demand)) {
            return new self($page);
        }

        $sourceHost = $demand['source_host'] ?? '';
        $sourcePath = $demand['source_path'] ?? '';
        $title = $demand['title'] ?? '';
        $statusCode = (int)($demand['target_statuscode'] ?? 0);
        $target = $demand['target'] ?? '';
        $orderType = $demand['orderType'] ?? '';
        $orderBy = $demand['orderBy'] ?? '';
        return new self($page, $sourceHost, $sourcePath, $target, $statusCode, $title,$orderBy,$orderType );
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public function hasTitle(): bool
    {
        return $this->title !== '';
    }

    /**
     * @return bool
     */
    public function hasOrderBy(): bool
    {
        return $this->orderBy !== '';
    }

    /**
     * @return bool
     */
    public function hasOrderType(): bool
    {
        return $this->orderType !== '';
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        $parameters = parent::getParameters();
        if ($this->hasTitle()) {
            $parameters['title'] = $this->getTitle();
        }
        if($this->hasOrderBy())
            $parameters['orderBy'] = $this->getOrderBy();
        if($this->hasOrderType())
            $parameters['orderType'] = $this->getOrderType();
        return $parameters;
    }

    /**
     * @return bool
     */
    public function hasConstraints(): bool
    {
        return parent::hasConstraints() || $this->hasTitle();
    }

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
     * @return string
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * @return string
     */
    public function getOrderType(): string
    {
        return $this->orderType;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }

    /**
     * @param string $sourcePath
     */
    public function setSourcePath(string $sourcePath): void
    {
        $this->sourcePath = $sourcePath;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    protected const KEY_Page = 'page';
    protected const KEY_OrderField = 'orderField';
    protected const KEY_OrderDirection = 'orderDirection';
    protected const KEY_SourceHosts = 'sourceHosts';
    protected const KEY_Target = 'target';
    protected const KEY_StatusCodes = 'statusCodes';
    protected const KEY_SourcePath = 'sourcePath';
    protected const KEY_Title = 'title';
    protected const KEY_OrderBy = 'orderBy';
    protected const KEY_OrderType = 'orderType';
    public function toArray()
    {
        return [
            self::KEY_Page => $this->getPage() ?? '',
            self::KEY_OrderField => $this->getOrderField() ?? '',
            self::KEY_OrderDirection => $this->getOrderDirection()  ?? '',
            self::KEY_SourceHosts => $this->getSourceHosts()  ?? [],
            self::KEY_Target => $this->getTarget()  ?? '',
            self::KEY_StatusCodes => $this->getStatusCodes()  ?? [],
            self::KEY_SourcePath => $this->getSourcePath()  ?? '',
            self::KEY_Title => $this->getTitle()  ?? '',
            self::KEY_OrderBy => $this->getOrderBy()  ?? '',
            self::KEY_OrderType => $this->getOrderType()  ?? '',
        ];
    }

    public static function getInstanceFromArray(array $values)
    {
        return new DemandExt(
            $values[self::KEY_Page],
            $values[self::KEY_OrderField],
            $values[self::KEY_OrderDirection],
           [ $values[self::KEY_SourceHosts]],
            '$values[self::KEY_SourcePath]',
            $values[self::KEY_Target],
            [$values[self::KEY_StatusCodes]],
            0,
            null,
            $values[self::KEY_Title],
            $values[self::KEY_OrderBy],
            $values[self::KEY_OrderType]
        );
    }
}
