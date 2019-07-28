<?php

namespace App\Api\Model;

use Psr\Http\Message\{MessageInterface, StreamInterface};

/**
 * PSR-7 Message
 */
class Message implements MessageInterface
{
    /**
     * header values (with origin header name and value)
     * @var array
     */
    protected $headers;

    /**
     * header key names lower case wit origin header names
     * @var array
     */
    protected $headerNames;

    /**
     * protocol (default 1.1)
     * @var string
     */
    protected $protocol = '1.1';

    /**
     * @var StreamInterface
     */
    protected $stream;

    /**
     * get protocol version
     * 
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * get instance with set protocol version
     * 
     * @param string|float $version
     * 
     * @return self
     */
    public function withProtocolVersion($version): self
    {
        if ($version === $this->protocol) {
            return $this;
        }

        $copy = clone $this;
        $copy->protocol = $version;

        return $copy;
    }

    /**
     * get body
     * 
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        if (null === $this->stream) {
            $this->stream = Stream::create('');
        }
        return $this->stream;
    }

    /**
     * get instance with set body
     * 
     * @param StreamInterface $body
     * 
     * @return self
     */
    public function withBody(StreamInterface $body): self
    {
        if ($body === $this->stream) {
            return $this;
        }

        $copy = clone $this;
        $copy->stream = $body;
        return $copy;
    }

    /**
     * check if item has header
     * 
     * @param string $name
     * 
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    /**
     * get header values by name
     * 
     * @param string $name
     * 
     * @return array
     */
    public function getHeader($name): array
    {
        $nameLower = strtolower($name);
        if (!isset($this->headerNames[$nameLower])) {
            return [];
        }

        return $this->headers[$this->headerNames[$nameLower]];
    }

    /**
     * get header values as string
     * 
     * @param string $name
     * 
     * @return string
     */
    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * get headers
     * 
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * get instance with header
     * if already exist this header remove it
     * 
     * @param string $name
     * @param string $value
     * 
     * @return self
     */
    public function withHeader($name, $value): self
    {
        $nameLower = strtolower($name);
        $valueConverted = $this->validAndParseHeaderValue($value);

        $copy = clone $this;
        if (isset($this->headerNames[$nameLower])) {
            unset($this->headers[$this->headerNames[$nameLower]]);
        }

        $copy->headerNames[$nameLower] = $name;
        $copy->headers[$name] = $valueConverted;

        return $copy;
    }

    /**
     * get instace with added header
     * 
     * @param string $name
     * @param string $value
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public function withAddedHeader($name, $value): self
    {
        if (!is_string($name) || '' === $name) {
            throw new \InvalidArgumentException('Header name cannot be empty and should be of string type');
        }

        $copy = clone $this;
        $copy->setHeader($name, $value);

        return $copy;
    }

    /**
     * set headers
     * 
     * @param array $headers
     */
    public function setHeaders(array $headers) {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    /**
     * set single header
     * 
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        $nameLower = strtolower($name);
        $valueConverted = $this->validAndParseHeaderValue($value);

        if (isset($this->headerNames[$nameLower])) {
            $header = $this->headerNames[$nameLower];
            $this->headers[$header] = array_unique(array_merge($this->headers[$header], $valueConverted));
        } else {
            $this->headerNames[$nameLower] = $name;
            $this->headers[$name] = $valueConverted;
        }
    }

    /**
     * get instance without header
     * 
     * @param string $name
     * 
     * @return self
     */
    public function withoutHeader($name): self
    {
        $nameLower = strtolower($name);
        if (!isset($this->headerNames[$nameLower])) {
            return $this;
        }

        $copy = clone $this;
        unset($copy->headers[$this->headerNames[$nameLower]], $copy->headerNames[$nameLower]);
        return $copy;
    }

    /**
     * valid and parse (trim, convert to string if possible) header value
     *
     * @param mixed $value
     *
     * @return array
     * 
     * @throws \InvalidArgumentException
     */
    private function validAndParseHeaderValue($value): array
    {
        $finalHeaders = [];

        if (is_array($value)) {
            if (empty($value)) {
                throw new \InvalidArgumentException('Header cannot be empty');
            }
            foreach ($value as $item) {
                $finalHeaders[] = $this->validAndParseHeaderValue($item);
            }
            return $finalHeaders;
        }

        if (!is_string($value) && !is_numeric($value)) {
            throw new \InvalidArgumentException('Header must be of string or numeric type');
        }

        return [$value];
    }

    /**
     * valid and parse single header value (trim, convert to string if possible) header value
     *
     * @param mixed $value
     *
     * @return string
     */
    public function validAndParseSingleHeaderValue($value): string
    {
        return trim((string) $value);
    }
}