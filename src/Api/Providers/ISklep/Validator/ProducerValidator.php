<?php

namespace App\Api\Providers\ISklep\Validator;

use App\Api\Validator\ValidatorAbstract;

/**
 * Validator for producer data
 */
class ProducerValidator extends ValidatorAbstract
{
    /**
     * validate producer data from api
     * 
     * @param array $data
     * 
     * @return bool
     */
    public function valid(array $data): bool
    {
        if (!array_key_exists('id', $data) || null === $data['id'] || '' === $data['id']) {
            $this->errors[] = 'Id is required';
        }
        elseif (!is_int($data['id']) || 0 === $data['id']) {
            $this->errors[] = 'Id should be type of integer greater then zero';
        }
        
        if (!array_key_exists('name', $data) || null === $data['name'] || '' === $data['name']) {
            $this->errors[] = 'Name is required';
        }
        if (!is_string($data['name'])) {
            $this->errors[] = 'Name should be type of string';
        }
        
        if (array_key_exists('site_url', $data) && null !== $data['site_url'] && !is_string($data['site_url'])) {
            $this->errors[] = 'Site url should be type of string';
        }
        
        if (array_key_exists('logo_filename', $data) && null !== $data['logo_filename'] && !is_string($data['logo_filename'])) {
            $this->errors[] = 'Logo filename should be type of string';
        }
        
        if (!array_key_exists('ordering', $data) || null === $data['ordering'] || '' === $data['ordering']) {
            $this->errors[] = 'Ordering is required';
        }
        elseif (!is_int($data['ordering']) || 0 === $data['ordering']) {
            $this->errors[] = 'Ordering should be type of integer greater then zero';
        }
        
        if (array_key_exists('source_id', $data) && null !== $data['source_id'] && !is_string($data['source_id'])) {
            $this->errors[] = 'Source Id should be type of string';
        }
        
        if (!empty($this->errors)) {
            $this->isValid = false;
        }
        
        return $this->isValid;
    }
}
