<?php

declare(strict_types=1);

namespace QcRedirects\Domain\Model;

class Redirect
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $sourceHost = '*';

    /**
     * @var string
     */
    protected $sourcePath;

    /**
     * @var string
     */
    protected $target;

    /**
     * @var int
     */
    protected $startTime;

    /**
     * @var int
     */
    protected $endTime;

    /**
     * @var int
     */
    protected $isRegExp;

    /**
     * @var int
     */
    protected $targetStatusCode;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
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
    public function getStartTime(): int
    {
        return $this->startTime;
    }

    /**
     * @param int $startTime
     */
    public function setStartTime(int $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return int
     */
    public function getEndTime(): int
    {
        return $this->endTime;
    }

    /**
     * @param int $endTime
     */
    public function setEndTime(int $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return int
     */
    public function getIsRegExp(): int
    {
        return $this->isRegExp;
    }

    /**
     * @param int $isRegExp
     */
    public function setIsRegExp(int $isRegExp): void
    {
        $this->isRegExp = $isRegExp;
    }

    /**
     * @return int
     */
    public function getTargetStatusCode(): int
    {
        return $this->targetStatusCode;
    }

    /**
     * @param int $targetStatusCode
     */
    public function setTargetStatusCode(int $targetStatusCode): void
    {
        $this->targetStatusCode = $targetStatusCode;
    }


}
