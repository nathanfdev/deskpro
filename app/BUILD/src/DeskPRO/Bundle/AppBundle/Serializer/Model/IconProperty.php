<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Entity\IconProperty as IconPropertyEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class IconProperty.
 */
class IconProperty
{
    /**
     * @JMS\Type("integer")
     *
     * @var
     */
    protected $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $urn;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $urnNs;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $urnPath;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $options;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $url;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $blobAuthId;

    public function __construct(IconPropertyEntity $iconProperty)
    {
        $this->id      = $iconProperty->getId();
        $this->urn     = $iconProperty->getUrn();
        $this->urnNs   = $iconProperty->getUrnNs();
        $this->urnPath = $iconProperty->getUrnPath();
        $this->options = $iconProperty->getOptions();
        if ($iconProperty->getBlob()) {
            $this->url        = $iconProperty->getBlob()->getDownloadUrl(true, true);
            $this->blobAuthId = $iconProperty->getBlob()->getAuthId();
        }
    }
}
