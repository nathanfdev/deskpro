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

namespace Application\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract exporting custom def entity.
 *
 * Class AbstractCustomDef
 */
abstract class AbstractCustomDef extends AbstractImportModel
{
    /**
     * @var AbstractCustomDef
     */
    protected $parent;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $description;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={
     *   "text",
     *   "textarea",
     *   "toggle",
     *   "date",
     *   "datetime",
     *   "choice",
     *   "multichoice",
     *   "checkbox",
     *   "radio",
     *   "display",
     *   "hidden"
     * })
     */
    protected $widgetType;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $is_enabled = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $is_user_enabled = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $is_agent_field = false;

    /**
     * @var mixed
     *
     * @JMS\Type("string")
     */
    protected $default_value;

    /**
     * @var array
     *
     * @JMS\Type("array")
     */
    protected $options = [];

    /**
     * @var AbstractCustomDef[]
     *
     * @JMS\Type("array")
     *
     * @Assert\Valid()
     */
    protected $children = [];

    /**
     * Returns parent field.
     *
     * @return AbstractCustomDef
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent field.
     *
     * @param AbstractCustomDef $parent
     *
     * @return $this
     */
    public function setParent(AbstractCustomDef $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOid()
    {
        return ($this->parent ? $this->parent->getOid().'-' : '').parent::getOid();
    }

    /**
     * Returns title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns handler class.
     *
     * @return string
     */
    public function getWidgetType()
    {
        return $this->widgetType;
    }

    /**
     * Set handler class.
     *
     * @param string $widgetType
     *
     * @return $this
     */
    public function setWidgetType($widgetType)
    {
        $this->widgetType = $widgetType;

        return $this;
    }

    /**
     * Is enabled?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * Mark as enabled.
     *
     * @param bool $is_enabled
     *
     * @return $this
     */
    public function setAsEnabled($is_enabled)
    {
        $this->is_enabled = (bool) $is_enabled;

        return $this;
    }

    /**
     * Is user enabled?
     *
     * @return bool
     */
    public function isUserEnabled()
    {
        return $this->is_user_enabled;
    }

    /**
     * Mark as user enabled.
     *
     * @param bool $is_user_enabled
     *
     * @return $this
     */
    public function setAsUserEnabled($is_user_enabled)
    {
        $this->is_user_enabled = (bool) $is_user_enabled;

        return $this;
    }

    /**
     * Is agent only field?
     *
     * @return bool
     */
    public function isAgentField()
    {
        return $this->is_agent_field;
    }

    /**
     * Mark as agent only field.
     *
     * @param bool $is_agent_field
     *
     * @return $this
     */
    public function setAsAgentField($is_agent_field)
    {
        $this->is_agent_field = (bool) $is_agent_field;

        return $this;
    }

    /**
     * Returns default value.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * Set default value.
     *
     * @param mixed $default_value
     *
     * @return $this
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;

        return $this;
    }

    /**
     * Returns options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set options.
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Returns a collection of child custom def.
     *
     * @return AbstractCustomDef[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add a child custom def.
     *
     * @param AbstractCustomDef $custom_def
     *
     * @return $this
     */
    public function addCustomDef(AbstractCustomDef $custom_def)
    {
        $this->children[] = $custom_def;
        $custom_def->setParent($this);

        return $this;
    }
}
