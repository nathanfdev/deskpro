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
        $this->setReference('standard_theme', $stdTheme);

        $sidebarTheme = new ThemeSet();
        $sidebarTheme->setThemeId('sidebar');
        $manager->persist($sidebarTheme);
        $this->setReference('sidebar_theme', $sidebarTheme);

        $helpCenterTheme = new ThemeSet();
        $helpCenterTheme->setThemeId('helpcenter');
        $manager->persist($helpCenterTheme);
        $this->setReference('helpcenter_theme', $helpCenterTheme);

        /** @var ThemeSet $defaultTheme */
        $defaultTheme = $this->getReference(sprintf('%s_theme', $this->getDefaultThemeId()));

        // Insert brand
        $brand = new Brand();
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
        $var  = $this->container->get('settings_resolver')->getGlobalSettings(true)->get('install.with_theme', 'standard');
        $var2 = $this->container->get('deskpro.app_env')->getConfig('settings.install.with_theme', 'standard');
        var_dump(file_get_contents($this->container->get('deskpro.app_env')->getDpRoot().'/config/advanced/config.settings.php'));
        var_dump('INSTALL_WITH_THEME '.$var, $var2);

        return $var;
    }
}
