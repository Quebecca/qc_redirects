<?php

namespace QcRedirects\Controller;

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
        'inputLink', 'inputDateTime', 'checkboxToggle'
    ];

    /**
     * @var array|string[]
     */
    protected array $errorsTypes = [
        'emptyValue' => false,
        'invalidValue' => false,
        'syntaxError' => false,
        'duplicatedSourcePath' => false,
        'invalidField' => false,
        'readOnlyField' => false
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
        0 => 'source_host',
        1 => 'source_path',
        2 => 'target',
        3 => 'is_regexp'
    ];

    /**
     * @var array|string[]
     */
    protected array $allowedAdditionalFields  = [];

    /**
     * @var string
     */
    protected string $duplicatedSourcePath = '';

    public function __construct()
    {
        $this->allowedAdditionalFields = $GLOBALS['TCA']['sys_redirect']['columns'];

    }

    public function checkForInvalidFields(array $extraFields) : bool{
        $this->invalidFields = array_diff($extraFields, array_keys($this->allowedAdditionalFields));
        if(!empty($this->invalidFields)){
            $this->errorsTypes['invalidField'] = true;
            return false;
        }
        return true;
    }

    public function checkForReadOnlyFields(array $extraFields) : bool{
        // check if the field is on ReadOnly
        foreach ($extraFields as $field){
            if($this->allowedAdditionalFields[$field]['config']['readOnly']){
                $this->readOnlyFields [] = $field;
            }
        }
        if(!empty($this->readOnlyFields)){
            $this->errorsTypes['readOnlyField'] = true;
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
                $this->errorsTypes['emptyValue'] = true;
                return false;
            }
            $i++;
        }
        return true;
    }

    public function inputLinkVerify($value){
        //  return filter_var($value, FILTER_VALIDATE_URL);
        return true;
    }
    public function inputDateTimeVerify($value): bool
    {
        return (bool)strtotime($value) || $value == '';
    }

    public function checkboxToggleVerify($value): bool
    {
        return $value == 'true' || $value == 'false' || $value == '';
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
    public function setErrorsTypes(string $key, bool $value): void
    {
        $this->errorsTypes[$key] = $value;
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