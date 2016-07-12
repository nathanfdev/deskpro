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

namespace Application\ImportBundle\Entity;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Abstract exporting custom def entity.
 *
 * Class AbstractCustomDef
 */
abstract class AbstractCustomDef extends AbstractEntity
{
    /**
     * @var AbstractCustomDef
     */
    protected $parent;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $handler_class;

    /**
     * @var bool
     */
    protected $is_enabled = false;

    /**
     * @var bool
     */
    protected $is_user_enabled = false;

    /**
     * @var bool
     */
    protected $is_agent_field = false;

    /**
     * @var mixed
     */
    protected $default_value;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var Collection|AbstractCustomDef[]
     */
    protected $children;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->children = new Collection();
    }

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
    public function getHandlerClass()
    {
        return $this->handler_class;
    }

    /**
     * Set handler class.
     *
     * @param string $handler_class
     *
     * @return $this
     */
    public function setHandlerClass($handler_class)
    {
        $this->handler_class = $handler_class;

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
     * @return AbstractCustomDef[]|Collection
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
        $this->children->attach($custom_def);
        $custom_def->setParent($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'oid'             => $this->oid,
            'import_map_key'  => $this->import_map_key,
            'title'           => $this->title,
            'description'     => $this->description,
            'handler_class'   => $this->handler_class,
            'is_enabled'      => $this->is_enabled,
            'is_user_enabled' => $this->is_user_enabled,
            'is_agent_field'  => $this->is_agent_field,
            'default_value'   => $this->default_value,
            'options'         => $this->options,
            'children'        => $this->children->entitiesToArray(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('title', new Constraints\NotBlank())
            ->addPropertyConstraint('handler_class', new Constraints\Choice([
                'choices' => [
                    null,
                    CustomDefAbstract::HANDLER_CLASS_CHOICE,
                    CustomDefAbstract::HANDLER_CLASS_TOGGLE,
                    CustomDefAbstract::HANDLER_CLASS_DATE,
                    CustomDefAbstract::HANDLER_CLASS_DATETIME,
                    CustomDefAbstract::HANDLER_CLASS_DISPLAY,
                    CustomDefAbstract::HANDLER_CLASS_HIDDEN,
                    CustomDefAbstract::HANDLER_CLASS_TEXT,
                    CustomDefAbstract::HANDLER_CLASS_TEXTAREA,
                ],
            ]))
            ->addPropertyConstraint('children', new Constraints\Valid())
        ;
    }
}
