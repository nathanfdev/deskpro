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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\AppInstance;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="custom_def_task")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class CustomDefTask implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @Assert\NotNull()
     */
    protected $id = null;

    /**
     * @var AppInstance
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\AppInstance")
     * @ORM\JoinColumn(name="app_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $app;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $js_class;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $has_form_template = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $has_display_template = false;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $title = '';

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $description = '';

    /**
     * @var mixed
     * @ORM\Column(type="string", nullable=true)
     */
    protected $handler_class = null;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $options = [];

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $is_user_enabled = true;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $is_enabled = true;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $display_order = 0;

    /**
     * @var mixed
     * @ORM\Column(type="string", length=500)
     */
    protected $default_value = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $is_agent_field = false;

    /**
     * @var mixed
     */
    protected $handler_instance = null;

    /**
     * @var \Application\DeskPRO\CustomFields\FieldManager
     */
    public $field_manager = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AppInstance
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param AppInstance $app
     *
     * @return $this
     */
    public function setApp(AppInstance $app)
    {
        $this->setModelField('app', $app);

        return $this;
    }

    /**
     * @return string
     */
    public function getJsClass()
    {
        return $this->js_class;
    }

    /**
     * @param $js_class
     *
     * @return $this
     */
    public function setJsClass($js_class)
    {
        $this->setModelField('js_class', $js_class);

        return $this;
    }

    /**
     * @return bool
     */
    public function getHasFormTemplate()
    {
        return $this->has_form_template;
    }

    /**
     * @param bool $has_form_template
     *
     * @return $this
     */
    public function setHasFormTemplate($has_form_template)
    {
        $this->setModelField('has_form_template', $has_form_template);

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsAgentField()
    {
        return $this->is_agent_field;
    }

    /**
     * @param bool $is_agent_field
     *
     * @return $this
     */
    public function setIsAgentField($is_agent_field)
    {
        $this->setModelField('is_agent_field', $is_agent_field);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDisplayTemplate()
    {
        return $this->has_display_template;
    }

    /**
     * @param bool $has_display_template
     *
     * @return $this
     */
    public function setHasDisplayTemplate($has_display_template)
    {
        $this->setModelField('has_display_template', $has_display_template);

        return $this;
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
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

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
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->setModelField('description', $description);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHandlerClass()
    {
        return $this->handler_class;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        if ($this->handler_instance !== null) {
            return $this->handler_instance;
        }

        if ($this['handler_class'] == 'x') {
            $e = new \Exception();
            echo $e->getTraceAsString();
            exit;
        }

        if (!$this->handler_class) {
            $this->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
        }

        $class_name             = $this->handler_class;
        $this->handler_instance = new $class_name($this);

        return $this->handler_instance;
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
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->setModelField('options', $options);

        return $this;
    }

    /**
     * Get the value of an option, or a default value if none is set.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        if (!isset($this->options[$name])) {
            return $default;
        }

        return $this->options[$name];
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        // we need this trick because we want to track change in ::selfModelField call
        $options = $this->options;

        if ($value === null) {
            unset($options[$name]);
        } else {
            $options[$name] = $value;
        }

        $this->setModelField('options', $options);

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsUserEnabled()
    {
        return $this->is_user_enabled;
    }

    /**
     * @param bool $is_user_enabled
     *
     * @return $this
     */
    public function setIsUserEnabled($is_user_enabled)
    {
        $this->setModelField('is_user_enabled', $is_user_enabled);

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $is_enabled
     *
     * @return $this
     */
    public function setIsEnabled($is_enabled)
    {
        $this->setModelField('is_enabled', $is_enabled);

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param int $display_order
     *
     * @return $this
     */
    public function setDisplayOrder($display_order)
    {
        $this->setModelField('display_order', $display_order);

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
     *
     * @return $this
     */
    public function setDefaultValue($default_value)
    {
        $this->setModelField('default_value', $default_value);

        return $this;
    }
}
