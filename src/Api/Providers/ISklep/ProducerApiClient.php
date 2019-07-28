<?php

namespace App\Api\Providers\ISklep;

use App\Api\Model\Request;
use App\Api\Providers\ISklep\ApiClient;
use App\Api\Providers\ISklep\Converter\ProducerConverter;
use App\Api\Exception\ApiException;
use App\Model\Producer;

/**
 * Client for producers in iSklep API
 */
class ProducerApiClient extends ApiClient
{
    /**
     * get all producers collection
     * 
     * @return array
     * 
     * @throws ApiException
     */
    public function getAll(): array
    {
        $request = new Request(
                'GET',
                $this->getBaseUrl() . 'producers',
                $this->getBaseHeaders()
        );
        
        $response = $this->client->send($request);
        $responseMapped = $this->responseMapperFactory->create($response);

        // verify
        if ($responseMapped->isError()) {
            throw new ApiException('iSklep Api error: ' . $responseMapped->getError());
        }
        if (!isset($responseMapped->getData()['producers'])) {
            throw new ApiException('iSklep Api error: no producers data');
        }
        
        // convert
        $converter = new ProducerConverter();
        return $converter->convertProducerCollection($responseMapped->getData()['producers']);
    }
    
    /**
     * create one producer by api
     * 
     * @param array $producerData
     * 
     * @return Producer
     * 
     * @throws ApiException
     */
    public function createOne(array $producerData): Producer
    {
        $request = new Request(
                'POST',
                $this->getBaseUrl() . 'producers',
                $this->getBaseHeaders(),
                json_encode($producerData)
        );

        $response = $this->client->send($request);
        $responseMapped = $this->responseMapperFactory->create($response);

        // verify
        if ($responseMapped->isError()) {
            throw new ApiException('iSklep Api error: ' . $responseMapped->getError());
        }
        if (!isset($responseMapped->getData()['producer'])) {
            throw new ApiException('iSklep Api error: no producer data');
        }
        
        // convert
        $converter = new ProducerConverter();
        return $converter->convertProducer($responseMapped->getData()['producer']);
    }
    
}
