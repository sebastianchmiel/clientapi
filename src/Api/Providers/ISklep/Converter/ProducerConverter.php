<?php

namespace App\Api\Providers\ISklep\Converter;

use App\Model\Producer;
use App\Api\Validator\ValidatorAbstract;
use App\Api\Providers\ISklep\Validator\ProducerValidator;

/**
 * Producer converter 
 */
class ProducerConverter
{
    /**
     * @var ValidatorAbstract 
     */
    private $validator;
    
    
    public function __construct()
    {
        $this->validator = new ProducerValidator();
    }
    
    /**
     * convert single producer data to model
     * 
     * @param array $data
     * 
     * @return Producer
     * 
     * @throws \InvalidArgumentException
     */
    public function convertProducer(array $data): Producer 
    {
        if (!$this->validator->valid($data)) {
            throw new \InvalidArgumentException('Producer data is incorrect ('
                    . implode(', ', $this->validator->getErrors()) . ')');
        }
        
        return new Producer(
                $data['id'],
                $data['name'],
                $data['site_url'] ?? null,
                $data['logo_filename'] ?? null,
                $data['ordering'],
                $data['source_id'] ?? null
        );
    }
    
    /**
     * convert collection of producer data to model collection
     * 
     * @param array $collection
     * 
     * @return array
     */
    public function convertProducerCollection(array $collection): array
    {
        $resultCollection = [];
        
        foreach ($collection as $item) {
            $resultCollection[] = $this->convertProducer($item);
        }
        
        return $resultCollection;
    }
}
