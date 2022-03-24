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

declare(strict_types=1);

namespace QcRedirects\Controller\BackendSession;


use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackendSession
{
    /**
     * The backend session object
     *
     * @var BackendUserAuthentication
     */
    protected $sessionObject;

    /**
     * Unique key to store data in the session.
     * Overwrite this key in your initializeAction method.
     *
     * @var string
     */
    protected string $storageKey = 'qc_redirect_filterKey';

    public function __construct()
    {
        $this->sessionObject = $GLOBALS['BE_USER'];
    }

    /**
     * This function is used to store the session key
     * @param string $storageKey
     */
    public function setStorageKey(string $storageKey): void
    {
        $this->storageKey = $storageKey;
    }

    /**
     * Store a value in the session
     *
     * @param string $key
     * @param mixed $value
     */
    public function store(string $key, $value): void
    {
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        $sessionData[$key] = $value;
        $this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
    }

    /**
     * Delete a value from the session
     *
     * @param string $key
     */
    public function delete($key): void
    {
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        unset($sessionData[$key]);
        $this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
    }

    /**
     * Read a value from the session
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        return $sessionData[$key] ?? null;
    }
}
