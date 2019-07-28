<?php

namespace App\Api\Providers\ISklep\Mapper;

use Psr\Http\Message\ResponseInterface;
use App\Api\Providers\ISklep\Mapper\ResponseMapper;
use App\Api\Exception\ApiException;

/**
 * iSklep response factory
 */
class ResponseMapperFactory
{
    /**
     * create mapped iSklep api response
     * 
     * @param ResponseInterface $response
     * 
     * @return ResponseMapper
     * 
     * @throws ApiException
     */
    public function create(ResponseInterface $response): ResponseMapper 
    {
        $response->getBody()->seek(0);
        $content = json_decode((string)$response->getBody()->getContents(), true);
        
        if (!is_array($content)) {
            throw new ApiException('Api blank response');
        }
        
        if (
                !isset($content['version']) || 
                (isset($content['version']) && !is_string($content['version']))
        ) {
            throw new ApiException('Api wrong response (missing version)');
        }
        
        if (
                !isset($content['success']) || 
                (isset($content['success']) && !is_bool($content['success']))
        ) {
            throw new ApiException('Api wrong response (missing success)');
        }

        return new ResponseMapper(
                $content['version'],
                $content['success'],
                $content['data'] ?? null,
                $content['error'] ?? null,
                $response->getHeaderLine('X-API-RequestIdentifier')
        );
    }
}
