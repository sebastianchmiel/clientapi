<?php

namespace App\Api\Model;

use App\Api\Model\{Message, Uri};
use Psr\Http\Message\{RequestInterface, UriInterface};

/**
 * PSR-7 Request
 */
final class Request extends Message implements RequestInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string|null
     */
    private $requestTarget;

    /**
     * @var UriInterface
     */
    private $uri;


    /**
     * @param string $method
     * @param UriInterface|null $uri
     * @param array $headers
     * @param mixed $body
     * @param string $version
     */
    public function __construct(string $method, $uri, array $headers = [], $body = null, string $version = '1.1')
    {
        if (!($uri instanceof UriInterface)) {
            $uri = new Uri($uri);
        }

        $this->method = $method;
        $this->uri = $uri;
        $this->setHeaders($headers);
        $this->protocol = $version;
        if (!$this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        if ('' !== $body && null !== $body) {
            $this->stream = Stream::create($body);
        }
    }

    /**
     * get method
     * 
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * get instance with set method
     * 
     * @param string $method
     * 
     * @return self
     */
    public function withMethod($method): self
    {
        $copy = clone $this;
        $copy->method = $method;
        return $copy;
    }
    
    /**
     * get request target
     * 
     * @return string
     */
    public function getRequestTarget(): string
    {
        if (null !== $this->requestTarget) {
            return $this->requestTarget;
        }

        $target = $this->uri ? $this->uri->getPath() : '';
        if ('' === $target && null === $this->requestTarget) {
            $target = '/';
        }

        if ($this->uri && '' !== $this->uri->getQuery()) {
            $target .= '?' . ($this->uri ? $this->uri->getQuery() : '');
        }

        return $target;
    }

    /**
     * get instance with set request target
     * 
     * @param string $requestTarget
     * 
     * @return self
     */
    public function withRequestTarget($requestTarget): self
    {
        $copy = clone $this;
        $copy->requestTarget = $requestTarget;
        return $copy;
    }

    /**
     * get uri
     * 
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }
    
    /**
     * get instance with uri
     * 
     * @param UriInterface $uri
     * @param string $preserveHost
     * 
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $copy = clone $uri;
        $copy->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('Host')) {
            $copy->updateHostFromUri();
        }

        return $copy;
    }

    /**
     * update host from uri
     * 
     * @return void
     */
    private function updateHostFromUri(): void
    {
        $host = $this->uri->getHost();
        if ('' === $host) {
            return;
        }

        $port = $this->uri->getPort();
        if (null !== $port) {
            $host .= ':' . $port;
        }

        if (isset($this->headerNames['host'])) {
            $header = $this->headerNames['host'];
        } else {
            $header = 'Host';
            $this->headerNames['host'] = $header;
        }

        $this->headers = [$header => [$host]] + $this->headers;
    }
}