<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifestChanges;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("all")
 */
class GenericChange
{
    /**
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    private $module;

    /**
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    private $value;

    /**
     * @param $component
     * @param $changeType
     * @param $value
     */
    public function __construct($component, $changeType, $value)
    {
        $this->module = $component;
        $this->type   = $changeType;
        $this->value  = $value;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getChangeType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
