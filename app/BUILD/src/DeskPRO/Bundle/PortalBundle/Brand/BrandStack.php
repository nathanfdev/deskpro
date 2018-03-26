<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Brand;

use Application\DeskPRO\Entity\Brand;

/**
 * The BrandStack is a way of managing changes in "active" Brands during runtime. It works similar to a stack to allow
 * pushing into and popping out of brands during runtime. However, it keeps an internal state of the constructed
 * BrandContainer's so that each BrandContainer only need be created once during a single request, even if you pop in
 * and out of different brands multiple times.
 *
 * This allows us to operate in a brand context and revert back to the old brand context without caring about how that
 * is done.
 *
 * Ex. use in a service that depends on this stack
 *    public function mailMarketingPromo(Brand $brand)
 *    {
 *         $this->brandStack->push($brand) // will construct the brand container if it doesnt exist
 *         $bc = $this->brandStack->getActive()
 *
 *         // email using settings and data from the $bc
 *
 *         $this->brandStack->pop() // revert the stack so that our service doesn't interrupt others
 *     }
 *
 * Any service / controller that wants to work with a brand (settings/templating/etc) should simply depend on this
 * BrandStack and use getActive(). Pop in an out of different brands as necessary.
 */
class BrandStack
{
    /**
     * @var BrandContainerFactory
     */
    private $factory;

    /**
     * @var \array
     */
    private $stack;

    /**
     * @var BrandContainer[] an array of constructed containers keyed by brand entity id
     */
    private $brandContainers;

    /**
     * @var \Application\DeskPRO\Entity\Brand
     */
    private $defaultBrand;

    /**
     * @param BrandContainerFactory $factory
     * @param Brand                 $default_brand
     */
    public function __construct(BrandContainerFactory $factory, Brand $default_brand)
    {
        $this->factory         = $factory;
        $this->stack           = [];
        $this->brandContainers = [];
        $this->defaultBrand    = $default_brand;

        $this->push($default_brand);
    }

    /**
     * Gives you the active BrandContainer.
     *
     * @return BrandContainer
     */
    public function getActive()
    {
        $brandId = end($this->stack);

        if (false !== $brandId) {
            return $this->brandContainers[$brandId];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getStack()
    {
        return $this->stack;
    }

    public function getContainers()
    {
        return $this->brandContainers;
    }

    /**
     * @return Brand
     */
    public function getDefaultBrand()
    {
        return $this->defaultBrand;
    }

    /**
     * Pushes the Brand into the stack, so that the brand's container is now active.
     *
     * @param Brand $brand
     * @param bool  $force
     *
     * @return BrandContainer
     */
    public function push(Brand $brand, $force = false)
    {
        $brandId = $brand->getId();

        if (!in_array($brandId, $this->stack) || $force) {
            array_push($this->stack, $brandId);

            if (!array_key_exists($brandId, $this->brandContainers)) {
                $this->brandContainers[ $brandId] = $this->factory->create($brand);
            }
        }

        return $this->getActive();
    }

    /**
     * Reverts pops the state, making the previous brand container active.
     *
     * @return BrandContainer The brand that was removed
     */
    public function pop()
    {
        $brandId = array_pop($this->stack);

        // always at least the default brand
        if (empty($this->stack)) {
            $this->push($this->defaultBrand);
        }

        return $this->brandContainers[ $brandId];
    }
}
