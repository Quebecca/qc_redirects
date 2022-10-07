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

namespace QcRedirects\Controller\ExtendedRedirectModule;

use TYPO3\CMS\Redirects\Repository\Demand;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Demand Object for filtering redirects in the backend module
 * @internal
 */
class DemandExt extends Demand
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

    /**
     * Demand constructor.
     * @param int $page
     * @param string $sourceHost
     * @param string $sourcePath
     * @param string $target
     * @param int $statusCode
     */
    public function __construct(int $page = 1, string $sourceHost = '', string $sourcePath = '', string $target = '', int $statusCode = 0, string $title = '', string $orderBy = '', string $orderType = '')
    {
        parent::__construct();

        $this->setPage($page);
        $this->setSourceHost($sourceHost);
        $this->setSourcePath($sourcePath);
        $this->setTarget($target);
        $this->setStatusCode($statusCode);

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
    public function getSourceHost(): string
    {
        return $this->sourceHost;
    }

    /**
     * @param string $sourceHost
     */
    public function setSourceHost(string $sourceHost): void
    {
        $this->sourceHost = $sourceHost;
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
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
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




}
