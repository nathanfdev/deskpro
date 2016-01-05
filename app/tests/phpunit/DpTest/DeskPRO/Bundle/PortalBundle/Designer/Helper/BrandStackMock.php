<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DpTest\DeskPRO\Bundle\PortalBundle\Designer\Helper;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;

/**
 * Class BrandStackMock.
 */
trait BrandStackMock
{
    /**
     * @param int|string $theme_set_id
     *
     * @return BrandStack
     */
    public function mockBrandStack($theme_set_id = null)
    {
        if (is_null($theme_set_id)) {
            $theme_set_id = uniqid('theme_set_');
        }

        $brand = new Brand();
        $brand->setName('Test Brand');
        $brand->setThemeSet($theme_set = new ThemeSet());
        $brand->setEditThemeSet($edit_theme_set = new ThemeSet());
        $theme_set->setThemeId($theme_set_id);
        $edit_theme_set->setThemeId("edit_$theme_set_id");

        $em = $this->getEntityManager();
        $em->persist($brand);
        $em->persist($theme_set);
        $em->persist($edit_theme_set);
        $em->flush();

        /** @var BrandContainer $brand_container */
        $brand_container = $this->getMockBuilder(BrandContainer::class)->disableOriginalConstructor()->getMock();
        $brand_container->method('getBrand')->willReturn($brand);

        /** @var BrandStack $brand_stack */
        $brand_stack = $this->getMockBuilder(BrandStack::class)->disableOriginalConstructor()->getMock();
        $brand_stack->method('getActive')->willReturn($brand_container);
        $brand_stack->method('getCurrentThemeSet')->willReturn($theme_set);
        $brand_stack->method('getCurrentEditThemeSet')->willReturn($edit_theme_set);

        return $brand_stack;
    }

    /**
     * @return EntityManager
     */
    abstract protected function getEntityManager();

    /**
     * @param string $class
     *
     * @return \PHPUnit_Framework_MockObject_MockBuilder
     */
    abstract protected function getMockBuilder($class);
}
