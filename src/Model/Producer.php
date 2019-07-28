<?php

namespace App\Model;

/**
 * Producer
 */
class Producer 
{
    /**
     * @var int
     */
    private $id;
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string|null
     */
    private $siteUrl;
    
    /**
     * @var string|null
     */
    private $logoFilename;
    
    /**
     * @var int
     */
    private $ordering;
    
    /**
     * @var string|null
     */
    private $sourceId;
    
    /**
     * @param int $id
     * @param string $name
     * @param string|null $siteUrl
     * @param string|null $logoFilename
     * @param int $ordering
     * @param string|null $sourceId
     */
    public function __construct(
        int $id,
        string $name,
        ?string $siteUrl,
        ?string $logoFilename,
        int $ordering,
        ?string $sourceId
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->siteUrl = $siteUrl;
        $this->logoFilename = $logoFilename;
        $this->ordering = $ordering;
        $this->sourceId = $sourceId;
    }
}
