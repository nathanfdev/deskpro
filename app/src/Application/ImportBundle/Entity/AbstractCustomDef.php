<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Entity;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class CustomDef
 * @package Application\ImportBundle\Entity
 */
abstract class AbstractCustomDef extends AbstractEntity
{
    /**
     * @var string
     */
    protected $sys_name;

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
    protected $options = array();

    /**
     * @var Collection|AbstractCustomDef[]
     */
    protected $custom_def;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->custom_def = new Collection();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->handler_class;
    }

    /**
     * @param string $handler_class
     * @return $this
     */
    public function setHandlerClass($handler_class)
    {
        $this->handler_class = $handler_class;
        return $this;
    }

    /**
     * @return string
     */
    public function getSysName()
    {
        return $this->sys_name;
    }

    /**
     * @param string $sys_name
     * @return $this
     */
    public function setSysName($sys_name)
    {
        $this->sys_name = $sys_name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $is_enabled
     * @return $this
     */
    public function setAsEnabled($is_enabled)
    {
        $this->is_enabled = (bool)$is_enabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isUserEnabled()
    {
        return $this->is_user_enabled;
    }

    /**
     * @param boolean $is_user_enabled
     * @return $this
     */
    public function setAsUserEnabled($is_user_enabled)
    {
        $this->is_user_enabled = (bool)$is_user_enabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAgentField()
    {
        return $this->is_agent_field;
    }

    /**
     * @param boolean $is_agent_field
     * @return $this
     */
    public function setAsAgentField($is_agent_field)
    {
        $this->is_agent_field = (bool)$is_agent_field;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * @param mixed $default_value
     * @return $this
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Returns a collection of child custom def
     *
     * @return AbstractCustomDef[]|Collection
     */
    public function getChildren()
    {
        return $this->custom_def;
    }

    /**
     * Add a child custom def
     *
     * @param AbstractCustomDef $custom_def
     * @return $this
     */
    public function addCustomDef(AbstractCustomDef $custom_def)
    {
        $this->custom_def->attach($custom_def);
        return $this;
    }

    /**
     * Set a collection of custom def
     *
     * @param Collection|AbstractCustomDef[] $custom_def
     * @return $this
     */
    public function setCustomDef(Collection $custom_def)
    {
        $this->custom_def = $custom_def;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array(
            'oid'             => $this->oid,
            'sys_name'        => $this->sys_name,
            'title'           => $this->title,
            'description'     => $this->description,
            'handler_class'   => $this->handler_class,
            'is_enabled'      => $this->is_enabled,
            'is_user_enabled' => $this->is_user_enabled,
            'is_agent_field'  => $this->is_agent_field,
            'default_value'   => $this->default_value,
            'options'         => $this->options,
            'custom_def'      => $this->custom_def->entitiesToArray(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('title', new Constraints\NotBlank())
            ->addPropertyConstraint('handler_class', new Constraints\NotBlank())
            ->addPropertyConstraint('custom_def', new Constraints\Valid())
        ;
    }
}
