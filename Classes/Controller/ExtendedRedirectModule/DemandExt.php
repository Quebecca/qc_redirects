<?php

declare(strict_types=1);


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
     * Demand constructor.
     * @param int $page
     * @param string $sourceHost
     * @param string $sourcePath
     * @param string $target
     * @param int $statusCode
     */
    public function __construct(int $page = 1, string $sourceHost = '', string $sourcePath = '', string $target = '', int $statusCode = 0, string $title = '')
    {
        parent::__construct($page,$sourceHost,$sourcePath,$target,$statusCode);
        $this->title = $title;
    }

    /**
     * Creates a Demand object from the current request.
     *
     * @param ServerRequestInterface $request
     * @return Demand
     */
    public static function createFromRequest(ServerRequestInterface $request): Demand
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
        return new self($page, $sourceHost, $sourcePath, $target, $statusCode, $title);
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
     * @return array
     */
    public function getParameters(): array
    {
        $parameters [] = parent::getParameters();
        if ($this->hasTitle()) {
            $parameters['title'] = $this->getTitle();
        }
        return $parameters;
    }

    /**
     * @return bool
     */
    public function hasConstraints(): bool
    {
        return parent::hasConstraints() || $this->hasTitle();
    }
}
