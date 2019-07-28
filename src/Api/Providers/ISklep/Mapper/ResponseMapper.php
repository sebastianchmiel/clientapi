<?php

namespace App\Api\Providers\ISklep\Mapper;

/**
 * iSklep api response model
 */
class ResponseMapper
{
    /**
     * api version
     * @var string
     */
    private $version;
    
    /**
     * flag define success
     * @var bool
     */
    private $success;
    
    /**
     * data
     * @var array|null
     */
    private $data;
    
    /**
     * error message
     * @var array|null
     */
    private $error;
    
    /**
     * request identifier
     * @var string
     */
    private $requestIdentifier;
    
    /**
     * @param string $version
     * @param bool $success
     * @param type $data
     * @param string|null $error
     * @param string $requestIdentifier
     */
    public function __construct(
            string $version,
            bool $success,
            $data, 
            ?array $error = null,
            string $requestIdentifier = ''
    ) {
        $this->version = $version;
        $this->success = $success;
        $this->data = $data;
        $this->error = $error;
        $this->requestIdentifier = $requestIdentifier;
    }
    
    /**
     * check is error
     * 
     * @return bool
     */
    public function isError(): bool
    {
        return !$this->success || null !== $this->error;
    }
    
    /**
     * get error message
     * 
     * @return string|null
     */
    public function getError(): ?string
    {
        if (empty($this->error)) {
            return $this->error;
        }
        
        return ($this->error['reason_code'] ?? '') 
            . ' (' . implode(', ', $this->error['messages'] ?? []) . ')';
    }
    
    /**
     * get data
     * 
     * @return mixed
     */
    public function getData() 
    {
        return $this->data;
    }
    
}
