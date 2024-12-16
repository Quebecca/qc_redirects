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
namespace Qc\QcRedirects\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ImportFormValidator
{
    /**
     * @var int
     */
    protected int $wrongValuekey = -1;

    /**
     * @var array|string[]
     */
    protected array $rowsConstraints = [];

    /**
     * @var array|string[]
     */
    protected array $checkingRules = [
        'inputLink', 'datetime', 'checkboxToggle', 'selectSingle'
    ];

    /**
     * @var array|string[]
     */
    protected array $errorsTypes =[
        'emptyValue' => [false],
        'invalidValue' => [false],
        'syntaxError' => [false],
        'duplicatedSourcePath' => [false],
        'invalidField' => [false],
        'readOnlyField' => [false]
    ];

    /**
     * @var array
     */
    protected array $invalidFields = [];

    /**
     * @var array
     */
    protected array $readOnlyFields = [];

    /**
     * @var array|string[]
     */
    protected array $mandatoryFields = [
        0 => 'source_path',
        1 => 'target',
        2 => 'is_regexp'
    ];

    /**
     * @var array|string[]
     */
    protected array $allowedAdditionalFields  = [];

    /**
     * @var string
     */
    protected string $duplicatedSourcePath = '';
    /**
     * @var LocalizationUtility
     */
    protected LocalizationUtility $localizationUtility;

    protected const LANG_FILE = 'LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:';

    public function __construct()
    {
        $this->localizationUtility ??= GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->allowedAdditionalFields = $GLOBALS['TCA']['sys_redirect']['columns'];
    }

    /**
     * @param array $extraFields
     * @return bool
     */
    public function checkForInvalidFields(array $extraFields) : bool{
        $this->invalidFields = array_diff($extraFields, array_keys($this->allowedAdditionalFields));
        if(!empty($this->invalidFields)){
            $this->setErrorsTypes(
                'invalidField',
                true,
                " '".implode(', ', $this->invalidFields)."' "
            );
            return false;
        }
        return true;
    }

    /**
     * @param array $extraFields
     * @return bool
     */
    public function checkForReadOnlyFields(array $extraFields) : bool{
        // check if the field is on ReadOnly
        foreach ($extraFields as $field){

            if($this->allowedAdditionalFields[$field]['config']['readOnly'] ??  false){
                $this->readOnlyFields [] = $field;
            }
        }
        if(!empty($this->readOnlyFields)){
            $this->setErrorsTypes(
                'readOnlyFields',
                true,
                implode(', ', $this->getReadOnlyFields())
            );
            return false;
        }
        return true;
    }


    /**
     * This function is used to verify the values of the columns
     * @param array $row
     * @return bool
     */
    public function verifyMandatoryColumnsExistence(array $row) : bool {
        $i = 0;
        foreach ($this->mandatoryFields as $fieldName){
            if(empty($row[$fieldName])){
                $this->wrongValuekey = $i;
                $this->setErrorsTypes(
                    'emptyValue',
                    true,
                    " ' ".$this->getRowsConstraints()[$this->getWrongValuekey()]." '"
                );
                return false;
            }
            $i++;
        }
        return true;
    }

    /**
     * @param $value
     * @return true
     */
    public function inputLinkVerify($value){
        //  return filter_var($value, FILTER_VALIDATE_URL);
        return true;
    }


    /**
     * @param string $key
     * @param $value
     * @return bool
     */
    public function datetimeVerify(string $key , $value): bool
    {
        return (bool) strtotime($value) || $value == '';
    }

    /**
     * @param string $key
     * @param $value
     * @return bool
     */
    public function checkboxToggleVerify(string $key, $value): bool
    {
        return $value == 'true' || $value == 'false' || $value == '';
    }

    /**
     * @param string $key
     * @param $value
     * @return bool
     */
    public function selectSingleVerify(string $key, $value) : bool {
        $availablValues = [];
        foreach ($this->allowedAdditionalFields[$key]['config']['items'] as $item){
            $availablValues[] = $item['value'] ?? $item[1];
        }
        return in_array($value, $availablValues);
    }

    /**
     * @return int
     */
    public function getWrongValuekey(): int
    {
        return $this->wrongValuekey;
    }

    /**
     * @param int $wrongValuekey
     */
    public function setWrongValuekey(int $wrongValuekey): void
    {
        $this->wrongValuekey = $wrongValuekey;
    }

    /**
     * @return array|string[]
     */
    public function getRowsConstraints(): array
    {
        return $this->rowsConstraints;
    }

    /**
     * @param array|string[] $rowsConstraints
     */
    public function setRowsConstraints(array $rowsConstraints): void
    {
        $this->rowsConstraints = $rowsConstraints;
    }

    /**
     * @return array|string[]
     */
    public function getCheckingRules(): array
    {
        return $this->checkingRules;
    }

    /**
     * @return array|string[]
     */
    public function getErrorsTypes(): array
    {
        return $this->errorsTypes;
    }

    /**
     * @param string $key
     * @param bool $value
     */
    public function setErrorsTypes(string $key, bool $value,string $message): void
    {
        $this->errorsTypes[$key][0] = $value;
        $this->errorsTypes[$key][1] = $this->localizationUtility->translate(self::LANG_FILE.$key) . $message;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string{
        foreach (array_values($this->getErrorsTypes()) as $errorType){
            if($errorType[0]){
                return $errorType[1];
            }
        }
        return '';
    }


    /**
     * @return array
     */
    public function getInvalidFields(): array
    {
        return $this->invalidFields;
    }

    /**
     * @return array
     */
    public function getReadOnlyFields(): array
    {
        return $this->readOnlyFields;
    }

    /**
     * @return array|string[]
     */
    public function getMandatoryFields(): array
    {
        return $this->mandatoryFields;
    }

    /**
     * @return array|string[]
     */
    public function getAllowedAdditionalFields()
    {
        return $this->allowedAdditionalFields;
    }

    /**
     * @return string
     */
    public function getDuplicatedSourcePath(): string
    {
        return $this->duplicatedSourcePath;
    }

    /**
     * @param string $duplicatedSourcePath
     */
    public function setDuplicatedSourcePath(string $duplicatedSourcePath): void
    {
        $this->duplicatedSourcePath = $duplicatedSourcePath;
    }


}
