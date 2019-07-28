<?php

namespace App\Api\Model;

use Psr\Http\Message\ResponseInterface;

/**
 * Response builder
 */
final class ResponseBuilder
{
    /**
     * response
     *
     * @var ResponseInterface
     */
    protected $response;
    
    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }
    
    /**
     * get response
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
    
    /**
     * add header from array
     *
     * @param array $headers
     *
     * @return self
     *
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    public function setHeadersFromArray(array $headers)
    {
        $status = array_shift($headers);
        $this->setStatus($status);
        foreach ($headers as $headerLine) {
            $headerLine = trim($headerLine);
            if ('' === $headerLine) {
                continue;
            }
            $this->addHeader($headerLine);
        }
        return $this;
    }
    
    /**
     * set headers from string
     *
     * @param string $headers
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function setHeadersFromString($headers)
    {
        if (!(is_string($headers)
            || (is_object($headers) && method_exists($headers, '__toString')))
        ) {
            throw new \InvalidArgumentException(__METHOD__ . ' expects parameter 1 to be a string, '
                    . (is_object($headers) ? get_class($headers) : gettype($headers)) . ' given');
        }
        
        $this->setHeadersFromArray(explode("\r\n", $headers));
        return $this;
    }
    
    /**
     * set status
     *
     * @param string $statusLine
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus($statusLine)
    {
        $parts = explode(' ', $statusLine, 3);
        if (count($parts) < 2 || 0 !== strpos(strtolower($parts[0]), 'http/')) {
            throw new \InvalidArgumentException($statusLine . ' is not a valid HTTP status line');
        }
        
        $reasonPhrase = count($parts) > 2 ? $parts[2] : '';
        $this->response = $this->response
            ->withStatus((int) $parts[1], $reasonPhrase)
            ->withProtocolVersion(substr($parts[0], 5));
        
        return $this;
    }
    
    /**
     * add header
     *
     * @param string $headerLine
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function addHeader($headerLine)
    {
        $parts = explode(':', $headerLine, 2);
        if (2 !== count($parts)) {
            throw new \InvalidArgumentException($headerLine . ' is not a valid HTTP header line');
        }
        
        $name = trim($parts[0]);
        $value = trim($parts[1]);
        
        if ($this->response->hasHeader($name)) {
            $this->response = $this->response->withAddedHeader($name, $value);
        } else {
            $this->response = $this->response->withHeader($name, $value);
        }
        
        return $this;
    }
}
