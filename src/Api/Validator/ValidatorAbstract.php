<?php

namespace App\Api\Validator;

/**
 * Validator abstract for data from api
 */
abstract class ValidatorAbstract
{
    /**
     * @var bool
     */
    protected $isValid = true;
    
    /**
     * @var array
     */
    protected $errors = [];
    
    /**
     * validate producer data from api
     * 
     * @param array $data
     * 
     * @return bool
     */
    abstract public function valid(array $data): bool;
    
    /**
     * get errors
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
