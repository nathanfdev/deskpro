<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AppManifestSetting.
 */
class SettingText extends Setting
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("defaultValue")
     *
     * @var string
     */
    private $defaultValue;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\SettingValidator")
     *
     * @var SettingValidator
     */
    private $validator;

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string $defaultValue
     */
    public function setDefaultValue( $defaultValue )
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return SettingValidator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @param SettingValidator $validator
     */
    public function setValidator( $validator )
    {
        $this->validator = $validator;
    }

}
