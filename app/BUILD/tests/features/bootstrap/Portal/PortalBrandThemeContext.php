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
namespace DpBehat\Portal;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeRepository;
use DpBehat\RebootableContextInterface;

class PortalBrandThemeContext extends BasePortalContext implements RebootableContextInterface
{
    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var ThemeRepository
     */
    private $theme_repository;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function rebootContext()
    {
        $this->resetPortalBrandThemeContext();
    }

    public function resetPortalBrandThemeContext()
    {
        $this->brand_stack      = $this->getKernel()->getContainer()->get('brand_stack');
        $this->theme_repository = $this->getKernel()->getContainer()->get('theme_repository');
        $this->em               = $this->getKernel()->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @Given the default brand is using the :theme_id theme
     */
    public function theActiveBrandHasTheme($theme_id)
    {
        $theme_set = $this->em->getRepository('App:ThemeSet')->findOneBy(['theme_id' => $theme_id]);

        if ($brand = $this->em->getRepository(Brand::class)->findOneBy(compact('theme_set'))) {
            $this->brand_stack->push($brand);
        } else {
            $brand_container = $this->brand_stack->getActive();
            $brand           = $brand_container->getBrand();

            $brand->setThemeSet($theme_set);

            $this->em->persist($brand);
            $this->em->flush($brand);
        }
    }
}
