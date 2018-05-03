<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\TicketLayout\LayoutField as BaseLayoutField;
use JMS\Serializer\Annotation as JMS;

/**
 * Class LayoutField.
 */
class LayoutField
{
    /**
     * @var string
     *
     * @JMS\Exclude()
     */
    private $originalFieldId;

    /**
     * @var string
     *
     * @JMS\Exclude()
     */
    private $context;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $fieldType;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $fieldId;

    /**
     * @var LayoutFieldOptions
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LayoutFieldOptions")
     */
    private $options;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $required;

    /**
     * Constructor.
     *
     * @param BaseLayoutField $layoutField
     * @param string          $context
     */
    public function __construct(BaseLayoutField $layoutField, $context)
    {
        $this->originalFieldId = $layoutField->getFieldId();
        $this->fieldType       = $layoutField->getFieldType();
        $this->fieldId         = $layoutField->getId();
        $this->options         = new LayoutFieldOptions($layoutField);
        $this->context         = $context;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return string
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * @return bool
     */
    public function isAgent()
    {
        return $this->context === 'agent';
    }

    /**
     * @return string
     */
    public function getOriginalFieldId()
    {
        return $this->originalFieldId;
    }
}
