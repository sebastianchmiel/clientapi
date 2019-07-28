<?php

namespace App\Api\Model;

use Psr\Http\Message\UriInterface;

/**
 * PSR-7 Uri
 */
final class Uri implements UriInterface
{
    /**
     * @var string
     */
    private $scheme = '';

    /**
     * @var string
     **/
    private $userInfo = '';

    /**
     * @var string
     **/
    private $host = '';

    /**
     * @var int|null
     */
    private $port;

    /**
     * @var string
     */
    private $path = '';

    /**
     * @var string
     **/
    private $query = '';

    /**
     * @var string
     */
    private $fragment = '';

    /**
     * @param string $uri
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(string $uri = '')
    {
        if ('' !== $uri) {
            $parts = parse_url($uri);
            if (false === $parts) {
                throw new \InvalidArgumentException('Wrong url: ' .  $url);
            }

            // set values from url
            $this->scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
            $this->userInfo = $parts['user'] ?? '';
            $this->host = isset($parts['host']) ? strtolower($parts['host']) : '';
            $this->port = isset($parts['port']) ? $this->validPort($parts['port']) : null;
            $this->path = isset($parts['path']) ? $this->validPath($parts['path']) : '';
            $this->query = isset($parts['query']) ? $this->validQuery($parts['query']) : '';
            $this->fragment = isset($parts['fragment']) ? $this->validQuery($parts['fragment']) : '';
            if (isset($parts['pass'])) {
                $this->userInfo .= ':'.$parts['pass'];
            }
        }
    }

    /**
     * convert to string
     * 
     * @return string
     */
    public function __toString(): string
    {
        $uri = '';
        if ('' !== $this->scheme) {
            $uri .= $this->scheme.':';
        }
        if ('' !== $this->getAuthority()) {
            $uri .= '//'.$this->getAuthority();
        }
        if ('' !== $this->path) {
            $path = $this->path;
            if ('/' !== $path[0]) {
                if ('' !== $this->authority) {
                    $path = '/'.$path;
                }
            } elseif (isset($path[1]) && '/' === $path[1]) {
                if ('' === $this->authority) {
                    $path = '/'.ltrim($path, '/');
                }
            }
            $uri .= $path;
        }
        if ('' !== $this->query) {
            $uri .= '?'.$this->query;
        }
        if ('' !== $this->fragment) {
            $uri .= '#'.$this->fragment;
        }
        return $uri;
    }

    /**
     * get scheme
     * 
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * get authority
     * 
     * @return string
     */
    public function getAuthority(): string
    {
        if ('' === $this->host) {
            return '';
        }
        $authority = $this->host;
        if ('' !== $this->userInfo) {
            $authority = $this->userInfo.'@'.$authority;
        }
        if (null !== $this->port) {
            $authority .= ':'.$this->port;
        }
        
        return $authority;
    }

    /**
     * get user info
     * 
     * @return string
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * get host
     * 
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * get port
     * 
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * get path
     * 
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * get query
     * 
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * get fragment
     * 
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * get instace with set scheme
     * 
     * @param string $scheme
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public function withScheme($scheme): self
    {
        if (!is_string($scheme)) {
            throw new \InvalidArgumentException('Scheme must be type of string');
        }
        
        $scheme = strtolower($scheme);
        if ($this->scheme === $scheme) {
            return $this;
        }
        
        $copy = clone $this;
        $copy->scheme = $scheme;
        $copy->port = $copy->validPort($copy->port);
        
        return $copy;
    }

    /**
     * get instance with set user info
     * 
     * @param string $user
     * @param string $password
     * 
     * @return self
     */
    public function withUserInfo($user, $password = null): self
    {
        $info = $user;
        if ('' != $password) {
            $info .= ':'.$password;
        }
        if ($this->userInfo === $info) {
            return $this;
        }

        $copy = clone $this;
        $copy->userInfo = $info;
        return $copy;
    }

    /**
     * get instance with set host
     * 
     * @param string $host
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public function withHost($host): self
    {
        if (!is_string($host)) {
            throw new \InvalidArgumentException('Host must be type of string');
        }
        $host = strtolower($host);
        if ($this->host === $host) {
            return $this;
        }

        $copy = clone $this;
        $copy->host = $host;
        return $copy;
    }

    /**
     * get instance with set port
     * 
     * @param int|null $port
     * 
     * @return self
     */
    public function withPort($port): self
    {
        $port = $this->validPort($port);
        if ($this->port === $port) {
            return $this;
        }

        $copy = clone $this;
        $copy->port = $port;
        return $copy;
    }

    /**
     * get instance with set path
     * 
     * @param string|null $path
     * 
     * @return self
     */
    public function withPath($path): self
    {
        $path = $this->validPath($path);
        if ($this->path === $path) {
            return $this;
        }

        $copy = clone $this;
        $copy->path = $path;
        return $copy;
    }

    /**
     * get instance with set query
     * 
     * @param string|null $query
     * 
     * @return self
     */
    public function withQuery($query): self
    {
        $query = $this->validQuery($query);
        if ($this->query === $query) {
            return $this;
        }

        $copy = clone $this;
        $copy->query = $query;
        return $copy;
    }

    /**
     * get instance with set fragment
     * 
     * @param string|null $fragment
     * 
     * @return self
     */
    public function withFragment($fragment): self
    {
        $fragment = $this->validQuery($fragment);
        if ($this->fragment === $fragment) {
            return $this;
        }

        $copy = clone $this;
        $copy->fragment = $fragment;
        return $copy;
    }

    /**
     * validate port
     * 
     * @param string|int|null $port
     * 
     * @return int|null
     */
    private function validPort($port): ?int
    {
        if (null === $port) {
            return null;
        }
        return (int) $port;
    }

    /**
     * validate path
     * 
     * @param string|null$path
     * 
     * @return string
     * 
     * @throws \InvalidArgumentException
     */
    private function validPath($path): string
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Path must be type of string');
        }
        return $path;
    }

    /**
     * validate query
     * 
     * @param string $str
     * 
     * @return string
     * 
     * @throws \InvalidArgumentException
     */
    private function validQuery($str): string
    {
        if (!is_string($str)) {
            throw new \InvalidArgumentException('Query must be type of string');
        }
        return $str;
    }
}