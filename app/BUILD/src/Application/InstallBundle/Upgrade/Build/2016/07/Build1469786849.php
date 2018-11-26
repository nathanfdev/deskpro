<?php

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\BrandSetting;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\PortalSettingsResolver;

class Build1469786849 extends AbstractBuild
{
    public function run()
    {
        $this->out('Copy brand settings from global settings');

        $portalSettingsResolver = new \ReflectionClass(PortalSettingsResolver::class);
        $entityManager          = $this->container->getEm();

        $settings = $portalSettingsResolver->getConstants();

        /** @var \DeskPRO\Bundle\BrandBundle\Brand\BrandStack $brandStack */
        $brandStack = $this->container->getBrandStack();

        $brand = $brandStack->getActive()->getBrand();

        /** @var SettingsResolver $settingsResolver */
        $settingsResolver = $this->container->get('settings_resolver');

        $globalSettings = $settingsResolver->getGlobalSettings();

        /** @var \Application\DeskPRO\EntityRepository\BrandSetting $brandSettingsRepository */
        $brandSettingsRepository = $entityManager->getRepository(BrandSetting::class);
        foreach ($settings as $setting) {
            if ($value = $globalSettings->get($setting)) {
                $brandSettingsRepository->updateSetting($setting, $value, $brand);
            }
        }

        $dataStoreRepository = $entityManager->getRepository(DataStore::class);

        /** @var DataStore $widgetData */
        $widgetData    = $dataStoreRepository->findOneBy(['name' => 'widget.brand_settings']);
        $newWidgetData = $dataStoreRepository->findOneBy(['name' => 'widget.brand_settings.'.$brand->getId()]);
        if ($widgetData && !$newWidgetData) {
            $widgetData->setName('widget.brand_settings.'.$brand->getId());

            $entityManager->persist($widgetData);
            $entityManager->flush();
        }
    }
}
