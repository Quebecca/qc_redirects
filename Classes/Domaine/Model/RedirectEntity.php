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

namespace QcRedirects\Domaine\Model;
class RedirectEntity
{
    /**
     * @var string
     */
    protected string $title = '';
    /**
     * @var string
     */
    protected string $sourceHost = '';
    /**
     * @var string
     */
    protected string $sourcePath = '';
    /**
     * @var string
     */
    protected string $target = '';
    /**
     * @var string
     */
    protected string $startTime = '';
    /**
     * @var string
     */
    protected string $endTime = '';
    /**
     * @var string
     */
    protected string $isRegExp = '';
    /**
     * @var int
     */
    protected int $statusCode;

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
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->startTime;
    }

    /**
     * @param string $startTime
     */
    public function setStartTime(string $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @return string
     */
    public function getEndTime(): string
    {
        return $this->endTime;
    }

    /**
     * @param string $endTime
     */
    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return string
     */
    public function getIsRegExp(): string
    {
        return $this->isRegExp;
    }

    /**
     * @param string $isRegExp
     */
    public function setIsRegExp(string $isRegExp): void
    {
        $this->isRegExp = $isRegExp;
    }


    public function getStatusCode(): ?int
    {
        return $this->statusCode ?? null;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }



}