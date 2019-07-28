<?php

namespace App\Api\Client;

use Psr\Http\Message\{RequestInterface, ResponseInterface};
use App\Api\Client\ClientInterface;
use App\Api\Exception\ApiException;
use App\Api\Model\{Response, ResponseBuilder};


/**
 * Curl client
 */
class Client implements ClientInterface
{
    /**
     * curl resource 
     * @var resource|null
     */
    private $handle;
    
    /**
     * cURL options
     *
     * @var array
     */
    private $curlOptions;
    
    /**
     * send a request
     * 
     * @param RequestInterface $request
     * 
     * @return ResponseInterface
     * 
     * @throws ApiException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        // init
        if (is_resource($this->handle)) {
            curl_reset($this->handle);
        } else {
            $this->handle = curl_init();
        }
        
        $responseBuilder = new ResponseBuilder(new Response());
        
        // set options
        curl_setopt_array($this->handle, $this->prepareRequestOptions($request));

        // execute
        $result = curl_exec($this->handle);
        $errno = curl_errno($this->handle);
        
        switch ($errno) {
            case CURLE_OK:
                // All OK, no actions needed
                break;
            case CURLE_COULDNT_RESOLVE_PROXY:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_COULDNT_CONNECT:
            case CURLE_OPERATION_TIMEOUTED:
            case CURLE_SSL_CONNECT_ERROR:
                throw new ApiException(curl_error($this->handle));
            default:
                throw new ApiException(curl_error($this->handle));
        }

        // split headers and body
        $headerLength = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);
        $headers = trim(substr($result, 0, $headerLength));
        $body = trim(substr($result, $headerLength));

        // put into response
        $responseBuilder->setHeadersFromString($headers);
        $responseBuilder->getResponse()->getBody()->write($body);
        
        return $responseBuilder->getResponse();
    }
    
    /**
     * set curl options
     * 
     * @param array $curlOptions
     * 
     * @return void
     */
    public function setOptions(array $curlOptions = []): void
    {
        $this->curlOptions = $curlOptions;
    }
    
    /**
     * prepare request options
     * 
     * @param RequestInterface $request
     * 
     * @return array
     * 
     * @throws ApiException
     */
    private function prepareRequestOptions(RequestInterface $request): array
    {
        $curlOptions = $this->curlOptions;
        
        // curl version
        try {
            $curlOptions[CURLOPT_HTTP_VERSION]
                = $this->getProtocolVersion($request->getProtocolVersion());
        } catch (\UnexpectedValueException $e) {
            throw new ApiException($e->getMessage());
        }

        // url
        $curlOptions[CURLOPT_URL] = (string)$request->getUri();
        
        // body
        $curlOptions = $this->addRequestBodyOptions($request, $curlOptions);
        
        // headers
        $curlOptions[CURLOPT_HTTPHEADER] = $this->createHeaders($request, $curlOptions);
        
        // user info
        if ($request->getUri()->getUserInfo()) {
            $curlOptions[CURLOPT_USERPWD] = $request->getUri()->getUserInfo();
        }

        return $curlOptions;
    }
    
    /**
     * get protocol version
     * 
     * @param string $requestVersion
     * 
     * @return int
     * 
     * @throws \UnexpectedValueException
     */
    private function getProtocolVersion(string $requestVersion): int
    {
        switch ($requestVersion) {
            case '1.0':
                return CURL_HTTP_VERSION_1_0;
            case '1.1':
                return CURL_HTTP_VERSION_1_1;
            case '2.0':
                if (defined('CURL_HTTP_VERSION_2_0')) {
                    return CURL_HTTP_VERSION_2_0;
                }
                throw new \UnexpectedValueException('libcurl 7.33 needed for HTTP 2.0 support');
        }
        return CURL_HTTP_VERSION_NONE;
    }
    
    /**
     * add rquest body options
     * 
     * @param RequestInterface $request
     * @param array $curlOptions
     * 
     * @return array
     */
    private function addRequestBodyOptions(RequestInterface $request, array $curlOptions): array
    {
        if (!in_array($request->getMethod(), ['GET', 'HEAD', 'TRACE'], true)) {
            $body = $request->getBody();
            $bodySize = $body->getSize();
            if ($bodySize !== 0) {
                if ($body->isSeekable()) {
                    $body->rewind();
                }

                if (null === $bodySize || $bodySize > 1024 * 1024) {
                    $curlOptions[CURLOPT_UPLOAD] = true;
                    if (null !== $bodySize) {
                        $curlOptions[CURLOPT_INFILESIZE] = $bodySize;
                    }
                    $curlOptions[CURLOPT_READFUNCTION] = function ($ch, $fd, $length) use ($body) {
                        return $body->read($length);
                    };
                } else {
                    $curlOptions[CURLOPT_POSTFIELDS] = (string)$body;
                }
            }
        }
        if ($request->getMethod() === 'HEAD') {
            $curlOptions[CURLOPT_NOBODY] = true;
        } elseif ($request->getMethod() !== 'GET') {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
        }
        return $curlOptions;
    }
    
    /**
     * create curl headers
     * 
     * @param RequestInterface $request
     * @param array $curlOptions
     * 
     * @return array
     */
    private function createHeaders(RequestInterface $request, array $curlOptions): array
    {
        $curlHeaders = [];
        $headers = $request->getHeaders();
        foreach ($headers as $name => $values) {
            $header = strtolower($name);
            if ('content-length' === $header) {
                if (array_key_exists(CURLOPT_POSTFIELDS, $curlOptions)) {
                    $values = [strlen($curlOptions[CURLOPT_POSTFIELDS])];
                } elseif (!array_key_exists(CURLOPT_READFUNCTION, $curlOptions)) {
                    $values = [0];
                }
            }
            foreach ($values as $value) {
                $curlHeaders[] = $name . ': ' . $value;
            }
        }
        
        return $curlHeaders;
    }
}
