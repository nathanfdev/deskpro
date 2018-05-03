<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AppManifestSetting.
 */
class SettingChoice extends Setting
{
    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("defaultValue")
     *
     * @var string
     */
    private $defaultValue;

    /**
     * @JMS\Type("boolean")
     *
     * @var boolean
     */
    private $multi;

    /**
     * @JMS\Type("array<DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\Choice>")
     *
     * @var array|Choice[]
     */
    private $choices;

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
     * @return bool
     */
    public function isMulti()
    {
        return $this->multi;
    }

    /**
     * @param bool $multi
     */
    public function setMulti( $multi )
    {
        $this->multi = $multi;
    }

    /**
     * @return array|Choice[]
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param array|Choice[] $choices
     */
    public function setChoices( $choices )
    {
        $this->choices = $choices;
    }
}
