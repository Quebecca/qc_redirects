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


namespace Qc\QcRedirects\Controller\BackendSession;

use __PHP_Incomplete_Class;
use phpDocumentor\Reflection\Types\String_;
use Qc\QcRedirects\Util\Arrayable;
use Qc\QcRedirects\Controller\ExtendedRedirectModule\v11\DemandExt;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendSession
{
    /**
     * The backend session object
     *
     * @var BackendUserAuthentication
     */
    protected $sessionObject;

    /** @var string[] */
    protected $registeredKeys = [];
    protected int $typoVersion;

    /**
     * Unique key to store data in the session.
     * Overwrite this key in your initializeAction method.
     *
     * @var string
     */
    protected $storageKey = 'qc_redirect_filterKey';

    public function __construct()
    {
        $this->sessionObject = $GLOBALS['BE_USER'];
        $this->registerFilterKey('qc_redirect_filterKey', DemandExt::class);
        $this->typoVersion = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion();

    }

    /**
     * This function is used to register keys
     * @param string $key
     * @param string $class
     */
    public function registerFilterKey(string $key, string $class): void
    {
        if (!$this->isClassImplementsInterface($class, Arrayable::class)) {
            throw new \InvalidArgumentException('Given class not instance of Arrayable');
        }
        $this->registeredKeys[$key] = $class;
    }

    /**
     * This function is used to verify if the class implements the interface Arrayable
     * @param string $class
     * @param string $interface
     * @return bool
     */
    protected function isClassImplementsInterface(string $class, string $interface): bool
    {
        $interfaces = class_implements($class);
        return ($interfaces && in_array($interface, $interfaces));
    }

    /**
     * @param $storageKey
     */
    public function setStorageKey($storageKey)
    {
        $this->storageKey = $storageKey;
    }

    /**
     * Store a value in the session
     *
     * @param string $key
     * @param mixed $value
     */
    public function store(string $key, $value)
    {

        if (!isset($this->registeredKeys[$key])) {
            throw new \InvalidArgumentException('Unknown key ' . $key);
        }
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        if ($this->typoVersion == 11) {
            $valueArray = $value->toArray();
            $sessionData[$key] = $valueArray;
        } else {
            $sessionData[$key] = $value;
        }
        $this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
    }

    /**
     * Delete a value from the session
     *
     * @param string $key
     */
    public function delete(string $key)
    {
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        unset($sessionData[$key]);
        $this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
    }

    /**
     * @param string $key
     * @return false|mixed|Arrayable|null
     */
    public function get(string $key)
    {
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);

        if (!isset($sessionData[$key]) || !$sessionData[$key]) {
            return null;
        }
        $result = $sessionData[$key];
        if($this->typoVersion == 10)
            return $result;
        // safeguard: check for incomplete class
        if (is_object($result) && $result instanceof \__PHP_Incomplete_Class) {
            $this->delete($key);
            return null;
        }
        if (is_object($result) && $result instanceof Arrayable) {
            return $result;
        }
        if (is_array($result) && isset($this->registeredKeys[$key])) {
            return call_user_func([$this->registeredKeys[$key], 'getInstanceFromArray'], $result);
        }
        return null;
    }
}
