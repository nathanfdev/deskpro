<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifestChanges;

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
     * @param $changeType
     * @param CustomField $value
     */
    public function __construct($changeType, CustomField $value)
    {
        $this->type  = $changeType;
        $this->value = $value;
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
}
