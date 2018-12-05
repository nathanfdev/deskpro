<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\BrandBundle\Brand;

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
     * @var DefaultBrandFinder
     */
    private $defaultBrandFinder;

    /**
     * @param BrandContainerFactory $factory
     * @param DefaultBrandFinder    $defaultBrandFinder
     */
    public function __construct(BrandContainerFactory $factory, DefaultBrandFinder $defaultBrandFinder)
    {
        $this->factory            = $factory;
        $this->stack              = [];
        $this->brandContainers    = [];
        $this->defaultBrandFinder = $defaultBrandFinder;
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

        if ($this->defaultBrandFinder->getDefaultBrand()) {
            return $this->factory->create($this->defaultBrandFinder->getDefaultBrand());
        }

        return;
    }

    /**
     * @return array
     */
    public function getStack()
    {
        return $this->stack;
    }

    /**
     * @return array|BrandContainer[]
     */
    public function getContainers()
    {
        return $this->brandContainers;
    }

    /**
     * @return Brand
     */
    public function getDefaultBrand()
    {
        return $this->defaultBrandFinder->getDefaultBrand();
    }

    /**
     * @return Brand
     */
    public function getDefaultBrandModel()
    {
        return $this->defaultBrandFinder->getDefaultBrandModel();
    }

    /**
     * @return BrandContainer
     */
    public function getDefaultBrandModelContainer()
    {
        return $this->factory->create($this->defaultBrandFinder->getDefaultBrandModel());
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
                $this->brandContainers[$brandId] = $this->factory->create($brand);
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
        if ($brandId && isset($this->brandContainers[$brandId])) {
            return $this->brandContainers[$brandId];
        }

        return $this->factory->create($this->defaultBrandFinder->getDefaultBrand());
    }

    /**
     * Temporarily push the Brand and runs $func, and then
     * pop the brand after.
     *
     * This will attempt to catch exceptions so the brand is always pop
     * afterwards.
     *
     * @param Brand    $brand
     * @param callback $func
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function pushTemporary(Brand $brand = null, $func = null)
    {
        if ($brand) {
            $this->push($brand, true);
        }

        $e   = null;
        $res = null;
        try {
            $res = $func($this, $brand);
        } catch (\Exception $e) {
        }

        if ($brand) {
            $this->pop();
        }

        if ($e) {
            throw $e;
        }

        return $res;
    }
}
