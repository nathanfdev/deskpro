<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\Brand as BrandEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Brand.
 */
class Brand
{
    /**
     * Brand id.
     *
     * @JMS\Type("integer")
     *
     * @var string
     */
    private $id;

    /**
     * Brand name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * Brand slug.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $slug;

    /**
     * Brand url.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $url;

    /**
     * Brand logo url.
     *
     * @JMS\Type("string")
     *
     * @var int
     */
    private $logoUrl;

    /**
     * Blob constructor.
     *
     * @param BrandEntity $brand
     * @param string      $logoUrl
     */
    public function __construct(BrandEntity $brand, $logoUrl = null)
    {
        $this->id      = $brand->getId();
        $this->name    = $brand->getName();
        $this->slug    = $brand->getSlug();
        $this->url     = $brand->getUrl();
        $this->logoUrl = $logoUrl;
    }
}
