<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppChanges;

use DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\CustomField;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("all")
 */
class CustomFieldChange
{
    /**
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    private $module = 'customFields';

    /**
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\CustomField")
     * @JMS\Expose()
     *
     * @var CustomField
     */
    private $value;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\CustomField")
     * @JMS\Expose()
     *
     * @var CustomField
     */
    private $previousValue;

    /**
     * @param $changeType
     * @param CustomField $value
     */
    public function __construct($changeType, CustomField $value = null, CustomField $previousValue = null)
    {
        $this->type  = $changeType;
        $this->value = $value;
        $this->previousValue = $previousValue;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return CustomField
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return CustomField
     */
    public function getPreviousValue()
    {
        return $this->previousValue;
    }
}
