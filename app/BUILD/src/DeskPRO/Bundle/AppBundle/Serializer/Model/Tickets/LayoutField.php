<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
