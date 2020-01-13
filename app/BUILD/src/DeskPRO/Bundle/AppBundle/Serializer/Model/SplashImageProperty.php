<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Entity\SplashImageProperty as SplashImagePropertyEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class SplashImageProperty.
 */
class SplashImageProperty
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

    public function __construct(SplashImagePropertyEntity $splashImageProperty)
    {
        $this->id      = $splashImageProperty->getId();
        $this->urn     = $splashImageProperty->getUrn();
        $this->urnNs   = $splashImageProperty->getUrnNs();
        $this->urnPath = $splashImageProperty->getUrnPath();
        $this->options = $splashImageProperty->getOptions();
        if ($splashImageProperty->getBlob()) {
            $this->url        = $splashImageProperty->getBlob()->getDownloadUrl(true, true);
            $this->blobAuthId = $splashImageProperty->getBlob()->getAuthId();
        }
    }
}
