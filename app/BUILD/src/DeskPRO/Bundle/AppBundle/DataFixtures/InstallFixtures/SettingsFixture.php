<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\BrandSetting;
use Application\DeskPRO\Entity\Setting;
use DeskPRO\Component\Util\RandUtils;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SettingsFixture extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $settings = [
            'core.done_data_initializer' => 1,
            'core.deskpro_build'         => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0,
            'core.install_build'         => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : time(),
            'core.install_timestamp'     => time(),
            'core.install_key'           => RandUtils::randomStringFormat('%25An'),
            'core.app_secret'            => RandUtils::randomStringFormat('%75An'),
            'core_tickets.use_ref'       => 1,
        ];

        $brandSettings = [
            'portal.widget.enabled' => 1,
            'core.apps_chat'        => 1,
        ];

        foreach ($settings as $name => $value) {
            $s        = $this->findOrCreate($name, $manager);
            $s->value = $value;
            $manager->persist($s);
        }

        $defaultBrandId = $manager->getRepository(Setting::class)->findOneBy(['name' => 'portal.default_brand'])->getValue();
        $brand          = $manager->getRepository(Brand::class)->findOneBy(['id' => $defaultBrandId]);
        foreach ($brandSettings as $name => $value) {
            $s        = $this->findOrCreateForBrand($name, $manager, $brand);
            $s->value = $value;
            $manager->persist($s);
        }

        $manager->flush();
    }

    /**
     * @param string        $name
     * @param ObjectManager $manager
     *
     * @return \Application\DeskPRO\Entity\Setting
     */
    private function findOrCreate($name, ObjectManager $manager)
    {
        $setting = $manager->getRepository(Setting::class)->findOneBy(['name' => $name]);
        if (!$setting) {
            $setting       = new Setting();
            $setting->name = $name;
        }

        return $setting;
    }

    /**
     * @param string        $name
     * @param ObjectManager $manager
     * @param Brand         $brand
     *
     * @return \Application\DeskPRO\Entity\Setting
     */
    private function findOrCreateForBrand($name, ObjectManager $manager, Brand $brand)
    {
        $setting = $manager->getRepository(BrandSetting::class)->findOneBy(['name' => $name, 'brand' => $brand]);
        if (!$setting) {
            $setting        = new BrandSetting();
            $setting->name  = $name;
            $setting->brand = $brand;
        }

        return $setting;
    }
}
