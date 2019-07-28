<?php

namespace App\Controller;

use App\Api\Client\Client;
use App\Api\Providers\ISklep\ProducerApiClient;

/**
 * Producer controller
 */
class ProducerController
{
    /**
     * @var ProducerApiClient
     */
    private $apiHandler;
    
    public function __construct()
    {
        // create client and api handler
        $client = new Client();
        $this->apiHandler = new ProducerApiClient(
                $client,
                getenv('API_ISKLEP_HOST'),
                getenv('API_ISKLEP_VERSION'),
                getenv('API_ISKLEP_USER'),
                getenv('API_ISKLEP_PASS')
        );
    }
    
    /**
     * get all
     */
    public function getAll()
    {
        try {
            $producerCollection = $this->apiHandler->getAll();
            dump($producerCollection);
        } catch (\Exception $ex) {
            echo 'Cannot get all items (' . $ex->getMessage() . ')';
        }
    }
    
    /**
     * create one producer
     */
    public function createOne()
    {
        try {
            $producer = $this->apiHandler->createOne(['producer' => [
                'name' => 'TestName',
                'site_url' => 'http://testUrl.pl',
                'logo_filename' => 'testFile.png',
                'source_id' => 'testId',
            ]]);
            dump($producer);
        } catch (\Exception $ex) {
            echo 'Cannot create producer (' . $ex->getMessage() . ')';
        }
    }
}

