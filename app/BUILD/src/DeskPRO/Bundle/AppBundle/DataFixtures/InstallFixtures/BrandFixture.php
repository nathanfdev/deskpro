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
        // Insert brand
        $brand = new Brand();

        // Insert themes
        $helpCenterTheme = new ThemeSet();
        $helpCenterTheme->setThemeId('helpcenter');
        $helpCenterTheme->setBrand($brand);

        $manager->persist($helpCenterTheme);
        $this->setReference('helpcenter_theme', $helpCenterTheme);

        /** @var ThemeSet $defaultTheme */
        $defaultTheme = $this->getReference(sprintf('%s_theme', $this->getDefaultThemeId()));

        $brand->setName('Default');
        $brand->setThemeSet($defaultTheme);
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

    public function getDefaultThemeId()
    {
        $defaultTheme = 'helpcenter';

        return $this->container->get('settings_resolver')->getGlobalSettings(true)->get('install.with_theme', $defaultTheme);
    }
}
