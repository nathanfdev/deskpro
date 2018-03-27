<?php

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
