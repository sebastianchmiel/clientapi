<?php

namespace App\Api\Providers\ISklep;

use App\Api\Client\ClientInterface;
use App\Api\Providers\ISklep\Mapper\ResponseMapperFactory;

/**
 * Client for iSklep API
 */
class ApiClient
{
    /**
     * @var string
     */
    private $host;
    
    /**
     * @var string
     */
    private $version;
    
    /**
     * @var string
     */
    private $user;
    
    /**
     * @var string
     */
    private $pass;
    
    /**
     * @var ClientInterface
     */
    protected $client;
    
    /**
     * isklep api response factory
     * @var ResponseMapperFactory
     */
    protected $responseMapperFactory;
    
    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client, string $host, string $version, string $user, string $pass)
    {
        $this->client = $client;
        $this->host = $host;
        $this->version = $version;
        $this->user = $user;
        $this->pass = $pass;

        // base options
        $client->setOptions([
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        
        $this->responseMapperFactory = new ResponseMapperFactory();
    }
    
    /**
     * get api base url
     * 
     * @return string
     */
    protected function getBaseUrl(): string
    {
        return $this->host . $this->version . '/';
    }
    
    /**
     * get api base http headers
     * 
     * @return array
     */
    public function getBaseHeaders(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->user.':'.$this->pass),
            'Content-Type' => 'application/json',
        ];
    }
}
