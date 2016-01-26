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
namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BrandFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // Insert themes
        $std_theme = new ThemeSet();
        $std_theme->setThemeId('standard');
        $manager->persist($std_theme);
        $this->setReference('std_theme', $std_theme);

        $sidebar_theme = new ThemeSet();
        $sidebar_theme->setThemeId('sidebar');
        $manager->persist($sidebar_theme);
        $this->setReference('sidebar_theme', $sidebar_theme);

        // Insert brand
        $brand = new Brand();
        $brand->setName('Default');
        $brand->setThemeSet($std_theme);
        $manager->persist($brand);
        $this->setReference('brand', $brand);

        $manager->flush();

        // Set default brand
        $setting        = new Setting();
        $setting->name  = 'portal.default_brand';
        $setting->value = $brand->getId();
        $manager->persist($brand);

        $manager->flush();
    }
}
