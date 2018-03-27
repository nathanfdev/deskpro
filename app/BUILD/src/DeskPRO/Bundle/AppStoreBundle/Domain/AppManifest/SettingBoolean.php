<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AppManifestSetting.
 */
class SettingBoolean extends Setting
{
    /**
     * @JMS\Type("boolean")
     * @JMS\SerializedName("defaultValue")
     *
     * @var boolean
     */
    private $defaultValue;

    /**
     * @return bool
     */
    public function isDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param bool $defaultValue
     */
    public function setDefaultValue( $defaultValue )
    {
        $this->defaultValue = $defaultValue;
    }
}
