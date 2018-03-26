<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class BrandFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 5;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // Insert themes
        $stdTheme = new ThemeSet();
        $stdTheme->setThemeId('standard');
        $manager->persist($stdTheme);
        $this->setReference('std_theme', $stdTheme);

        $sidebarTheme = new ThemeSet();
        $sidebarTheme->setThemeId('sidebar');
        $manager->persist($sidebarTheme);
        $this->setReference('sidebar_theme', $sidebarTheme);

        // Insert brand
        $brand = new Brand();
        $brand->setName('Default');
        $brand->setThemeSet($stdTheme);
        $manager->persist($brand);
        $this->setReference('brand', $brand);

        $manager->flush();

        // Set default brand
        $setting        = new Setting();
        $setting->name  = 'portal.default_brand';
        $setting->value = $brand->getId();
        $manager->persist($setting);

        $manager->flush();
    }
}
