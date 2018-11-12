<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppChanges;

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
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    private $previousValue;

    /**
     * @param $component
     * @param $changeType
     * @param $value
     * @param $previousValue
     */
    public function __construct($component, $changeType, $value = null, $previousValue = null)
    {
        $this->module = $component;
        $this->type   = $changeType;
        $this->value  = $value;
        $this->previousValue  = $previousValue;
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
     * @return string | null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string | null
     */
    public function getPreviousValue()
    {
        return $this->value;
    }
}
