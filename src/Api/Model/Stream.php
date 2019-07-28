<?php

namespace App\Api\Model;

use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 Stream
 */
final class Stream implements StreamInterface
{
    /** 
     * @var resource|null
     */
    private $stream;
    
    /**
     * @var bool 
     */
    private $seekable;
    
    /** 
     * @var bool 
     */
    private $readable;
    
    /** 
     * @var bool 
     */
    private $writable;
    
    /** 
     * @var mixed|null 
     */
    private $uri;
    
    /** 
     * @var int|null 
     */
    private $size;
    
    /**
     * readable and writiable strema types hashes
     * @var array
     */
    private const READ_WRITE_HASH = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];
    
    /**
     * create strem
     * 
     * @param string $body
     * 
     * @return StreamInterface
     * 
     * @throws \InvalidArgumentException
     */
    public static function create($body = ''): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }
        
        if (is_string($body)) {
            $resource = fopen('php://temp', 'rw+');
            fwrite($resource, $body);
            $body = $resource;
        }
        
        if (is_resource($body)) {
            $copy = new self();
            $copy->stream = $body;
            $meta = stream_get_meta_data($copy->stream);
            $copy->seekable = $meta['seekable'] && 0 === fseek($copy->stream, 0, SEEK_CUR);
            $copy->readable = isset(self::READ_WRITE_HASH['read'][$meta['mode']]);
            $copy->writable = isset(self::READ_WRITE_HASH['write'][$meta['mode']]);
            $copy->uri = $copy->getMetadata('uri');
            return $copy;
        }
        throw new \InvalidArgumentException('Body must be a string, resource or StreamInterface');
    }
    
    /**
     * convert object to string
     * 
     * @return string
     */
    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }
            return $this->getContents();
        } catch (\Exception $ex) {
            return '';
        }
    }

    /**
     * close stream
     * 
     * @return void
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    /**
     * detach stream
     * 
     * @return resource|null
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }
        
        $result = $this->stream;
        unset($this->stream);
        
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;
        return $result;
    }

    /**
     * check end of file
     * 
     * @return bool
     */
    public function eof(): bool
    {
        return !$this->stream || feof($this->stream);
    }

    /**
     * get contents
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Unable to read stream contents');
        }
        
        $contents = stream_get_contents($this->stream);
        if (false === $contents) {
            throw new \RuntimeException('Unable to read stream contents');
        }
        
        return $contents;
    }

    /**
     * get meta data
     * 
     * @param string $key
     * 
     * @return array
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        }
        
        $meta = stream_get_meta_data($this->stream);
        if (null === $key) {
            return $meta;
        }
        
        return $meta[$key] ?? null;
    }

    /**
     * get size
     * 
     * @return int
     */
    public function getSize()
    {
        if (null !== $this->size) {
            return $this->size;
        }
        
        if (!isset($this->stream)) {
            return null;
        }
        
        // clear
        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }
        
        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }
        
        return null;
    }

    /**
     * check is readable
     * 
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * check if is seekable
     * 
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * check if is writable
     * 
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * read 
     * 
     * @param int|null $length
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    public function read($length): string
    {
        if (!$this->readable) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }
        
        return fread($this->stream, $length);
    }

    /**
     * rewind
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * seek
     * 
     * @param int $offset
     * @param type $whence
     * 
     * @throws \RuntimeException
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        }
        
        if (-1 === fseek($this->stream, $offset, $whence)) {
            throw new \RuntimeException('Unable to seek to stream position ' . $offset);
        }
    }

    /**
     * tell
     * 
     * @return int
     * 
     * @throws \RuntimeException
     */
    public function tell(): int
    {
        $result = ftell($this->stream);
        if (false === $result) {
            throw new \RuntimeException('Unable to determine stream position');
        }
        
        return $result;
    }

    /**
     * write
     * 
     * @param string $string
     * 
     * @return int
     * 
     * @throws \RuntimeException
     */
    public function write($string): int
    {
        if (!$this->writable) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        $this->size = null;
        $result = fwrite($this->stream, $string);
        if (false === $result) {
            throw new \RuntimeException('Unable to write to stream');
        }
        return $result;
    }

}
