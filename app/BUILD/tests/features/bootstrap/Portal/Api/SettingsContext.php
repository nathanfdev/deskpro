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

/**
 * DeskPRO.
 */

namespace DpBehat\Portal\Api;

use Application\DeskPRO\Entity\DataStore;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\WidgetSettings;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;

/**
 * Class SettingsContext.
 */
class SettingsContext extends BaseContext
{
    /**
     * @Given :settingName widget brand chat setting is set to :value for :brand
     *
     * @param string $settingName
     * @param mixed  $value
     * @param int    $brand
     */
    public function setWidgetBrandChatSetting($settingName, $value, $brand)
    {
        $brandEntity   = DataContext::getReference($brand);
        $dataStoreName = 'widget.brand_settings.'.$brandEntity->getId();
        $dataStore     = $this
            ->em()
            ->getRepository(DataStore::class)
            ->findOneBy(['name' => $dataStoreName]);
        if (!$dataStore) {
            $dataStore = new DataStore();
            $dataStore->setName($dataStoreName);
        }
        /** @var WidgetSettings $model */
        $model        = $this->get('widget_settings_resolver')->getWidgetSettings($brandEntity);
        $brandSetting = $model->getSettings()->getBrand();
        $method       = 'set'.ucfirst($settingName);
        $brandSetting->getChat()->$method($value);
        $dataStore->setData('brand_settings', $brandSetting);
        $this->em()->persist($dataStore);
        $this->em()->flush();
    }
}
