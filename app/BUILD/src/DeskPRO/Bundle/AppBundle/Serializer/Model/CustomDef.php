<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\CustomDefAbstract;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CustomDef.
 */
class CustomDef
{
    /**
     * The unique ID.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var CustomDefAbstract
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\CustomDefAbstract>")
     */
    private $parent;

    /**
     * The title.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * The description.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $description;

    /**
     * Options for the field.
     *
     * @JMS\Type("array")
     */
    private $options;

    /**
     * Can the field be viewed by the user?
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $isUserEnabled;

    /**
     * True if field is enabled.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isEnabled;

    /**
     * Obviously it is field`s display order.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $displayOrder;

    /**
     * Is this field associated with agents only.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isAgentField;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $choices;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $widgetType;

    /**
     * Constructor.
     *
     * @param CustomDefAbstract $def
     */
    public function __construct(CustomDefAbstract $def)
    {
        $this->id            = $def->getId();
        $this->parent        = $def->getParent();
        $this->title         = $def->getRawTitle();
        $this->description   = $def->getRawDescription();
        $this->options       = $def->getOptions();
        $this->isUserEnabled = $def->isUserEnabled();
        $this->isEnabled     = $def->isEnabled();
        $this->displayOrder  = $def->getDisplayOrder();
        $this->isAgentField  = $def->isAgentField();
        $this->defaultValue  = $def->getDefaultValue();
        $this->widgetType    = $def->getWidgetType();
        $this->choices       = $def->getChoices();
    }
}
