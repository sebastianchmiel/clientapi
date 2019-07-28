<?php

namespace App\Api\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * client interface
 */
interface ClientInterface
{
    /**
     * send request and get response
     * 
     * @param RequestInterface $request
     * 
     * @return ResponseInterface
     */
    public function send(RequestInterface $request): ResponseInterface;
}
