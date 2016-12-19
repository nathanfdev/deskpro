<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\BrandSetting;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\PortalSettingsResolver;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;

class Build1469786849 extends AbstractBuild
{
    public function run()
    {
        $this->out('Copy brand settings from global settings');

        $portalSettingsResolver = new \ReflectionClass(PortalSettingsResolver::class);
        $entityManager          = $this->container->getEm();

        $settings = $portalSettingsResolver->getConstants();

        /** @var BrandStack $brandStack */
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
