<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

use Application\DeskPRO\Entity\Brand;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractBrandAwareSettings.
 */
abstract class AbstractBrandAwareSettings
{
    /**
     * Current Brand.
     *
     * @var Brand
     *
     * @JMS\Exclude()
     */
    protected $brand;

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     *
     * @return $this
     */
    public function setBrand(Brand $brand)
    {
        $this->brand = $brand;

        return $this;
    }
}
