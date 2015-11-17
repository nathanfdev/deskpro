<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\Handler\Date;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Doctrine\Common\Collections\ArrayCollection;
use Orb\Util\Numbers;

/**
 * A custom field definition.
 *
 * @property int $display_order
 * @property CustomDefAbstract|null $parent
 * @property CustomDefAbstract[]|null $children
 */
class CustomDefAbstract extends \Application\DeskPRO\Domain\DomainObject implements HasPhraseName
{
    const HANDLER_CLASS_TEXT     = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
    const HANDLER_CLASS_TEXTAREA = 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea';
    const HANDLER_CLASS_CHOICE   = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
    const HANDLER_CLASS_TOGGLE   = 'Application\\DeskPRO\\CustomFields\\Handler\\Toggle';
    const HANDLER_CLASS_DATE     = 'Application\\DeskPRO\\CustomFields\\Handler\\Date';
    const HANDLER_CLASS_DATETIME = 'Application\\DeskPRO\\CustomFields\\Handler\\Datetime';
    const HANDLER_CLASS_DISPLAY  = 'Application\\DeskPRO\\CustomFields\\Handler\\Display';
    const HANDLER_CLASS_HIDDEN   = 'Application\\DeskPRO\\CustomFields\\Handler\\Hidden';

    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Is the field associated with an app?
     * These generally cant be edited.
     *
     * @var \Application\DeskPRO\Entity\AppInstance
     */
    protected $app = null;

    /**
     * JS class to init.
     *
     * @var string
     */
    protected $js_class = '';

    /**
     * True if this field uses a custom template when rendering the form input.
     *
     * @var string
     */
    protected $has_form_template = false;

    /**
     * True i this field uses a custom template when rendering the form value for display.
     *
     * @var string
     */
    protected $has_display_template = false;

    /**
     * Field parent.
     *
     * MUST BE IMPLEMENT IN CHILD CLASS
     *
     * @var XXX
     */
    //protected $parent = null;

    /**
     * Field children.
     *
     * MUST BE IMPLEMENT IN CHILD CLASS
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    //protected $children = null;

    /**
     * The title.
     *
     * @var string
     */
    protected $title = '';

    /**
     * The description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The handler class.
     *
     * May be nullable if the def is a child representing some kind of option.
     * For example, a select box has children who we only need the 'title' for.
     *
     * @var string
     */
    protected $handler_class = null;

    /**
     * Options for the field.
     */
    protected $options = array();

    /**
     * Can the field be viewed by the user?
     *
     * @var bool
     */
    protected $is_user_enabled = true;

    /**
     * @var bool
     */
    protected $is_enabled = true;

    /**
     * @var int
     */
    protected $display_order = 0;

    /**
     * @var string
     */
    protected $default_value = null;

    /**
     * @var bool
     */
    protected $is_agent_field = false;

    /**
     * @var \Application\DeskPRO\CustomFields\Handler\HandlerAbstract
     */
    protected $_handler_instance = null;

    /**
     * @var \Application\DeskPRO\CustomFields\FieldManager
     */
    public $field_manager = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        if ($this->parent) {
            return $this->parent->getId();
        }

        return 0;
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
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return string
     */
    public function getRealTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return App::getTranslator()->getPhraseObject($this, 'title');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return App::getTranslator()->getPhraseObject($this, 'description');
    }

    /**
     * @return string
     */
    public function getRealDescription()
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
        $this->setModelField('description', $description);

        return $this;
    }

    /**
     * @return string
     */
    public function isUserEnabled()
    {
        return $this->is_user_enabled;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return CustomDefAbstract[]|null
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function hasChildren()
    {
        return count($this->children) > 0;
    }

    /**
     * @return CustomDefAbstract|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add a child to this field.
     *
     * @param CustomDefAbstract $def
     */
    public function addChild(CustomDefAbstract $def)
    {
        $this->children->add($def);
        $def['parent'] = $this;
        $this->_onPropertyChanged('children', $this->children, $this->children);
    }

    /**
     * Remove a child field.
     *
     * @param CustomDefAbstract $def
     */
    public function removeChild(CustomDefAbstract $def)
    {
        $this->children->removeElement($def);
        $this->_onPropertyChanged('children', $this->children, $this->children);
    }

    /**
     * Remove a child based on the childs field id.
     *
     * @param int $def_id
     */
    public function removeChildId($def_id)
    {
        foreach ($this->children as $k => $v) {
            if ($v['id'] == $def_id) {
                $this->children->remove($k);

                return;
            }
        }
        $this->_onPropertyChanged('children', $this->children, $this->children);
    }

    /**
     * @return $this
     */
    public function resetChildren()
    {
        $this->children->clear();

        return $this;
    }

    /**
     * @param int $def_id
     *
     * @return CustomDefAbstract
     */
    public function getChildById($def_id)
    {
        foreach ($this->children as $v) {
            if ($v->getId() == $def_id) {
                return $v;
            }
        }
    }

    /**
     * Set handler class.
     *
     * @param null $handler_class
     *
     * @return $this
     */
    public function setHandlerClass($handler_class = null)
    {
        $this->setModelField('handler_class', $handler_class);

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
     * Get the DeskPRO form field object that knows how to render data etc.
     *
     * @return \Application\DeskPRO\CustomFields\Handler\HandlerAbstract
     */
    public function getHandler()
    {
        if ($this->_handler_instance !== null) {
            return $this->_handler_instance;
        }

        if ($this['handler_class'] == 'x') {
            $e = new \Exception();
            echo $e->getTraceAsString();
            exit;
        }

        if (!$this->handler_class) {
            $this->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
        }

        $classname               = $this->handler_class;
        $this->_handler_instance = new $classname($this);

        return $this->_handler_instance;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|CustomDefAbstract[]
     */
    public function getAllChildren()
    {
        return $this->children;
    }

    /**
     * Get an array of all IDs from this def and down.
     *
     * @return array
     */
    public function getAllChildIds()
    {
        $ids = array($this->id);
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildIds());
        }

        return $ids;
    }

    /**
     * @return array
     */
    public function getAllChildTitles()
    {
        $titles = array();
        foreach ($this->children as $child) {
            $titles[$child->getId()] = $child->getTitle();
        }

        return $titles;
    }

    /**
     * Creates a new instance of the same type and sets its parent to this object.
     * Note that you should still add it to the tree with addField.
     *
     * @return CustomDefAbstract
     */
    public function createChild()
    {
        $obj           = new static();
        $obj['parent'] = $this;
        $this->children->add($obj);

        return $obj;
    }

    /**
     * Get the value of an option, or a default value if none is set.
     *
     * @param  $name
     * @param null $default
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
     * @return array
     */
    public function getAllOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getHtmlOption()
    {
        return App::getTranslator()->getPhraseObject($this, 'html');
    }

    /**
     * @return string
     */
    public function getRealHtmlOption()
    {
        return $this->getOption('html', '');
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
        $this->setModelField('options', $options);

        return $this;
    }

    /**
     * Set a value of an option.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        $old_opt = $this->options;

        if ($value === null) {
            unset($this->options[$name]);
        } else {
            $this->options[$name] = $value;
        }

        $this->_onPropertyChanged('options', $old_opt, $this->options);

        return $this;
    }

    /**
     * @param bool $isAgent
     *
     * @return mixed
     */
    public function isRequired($isAgent = false)
    {
        $option_name = ($isAgent ? 'agent_' : '').'required';

        return (bool) $this->getOption($option_name, false);
    }

    /**
     * @param bool $isAgent
     *
     * @return mixed
     */
    public function getMinLength($isAgent = false)
    {
        $option_name = ($isAgent ? 'agent_' : '').'min_length';

        return $this->getOption($option_name, 0);
    }

    /**
     * @param bool $isAgent
     *
     * @return mixed
     */
    public function getMaxLength($isAgent = false)
    {
        $option_name = ($isAgent ? 'agent_' : '').'max_length';

        return $this->getOption($option_name, 0);
    }

    /**
     * @param bool $isAgent
     *
     * @return mixed
     */
    public function getRegex($isAgent = false)
    {
        $option_name = ($isAgent ? 'agent_' : '').'regex';

        return $this->getOption($option_name, null);
    }

    public function getValidWeekDays()
    {
        return $this->getOption('date_valid_dow', null);
    }

    public function getDateMin()
    {
        $type = $this->getOption('date_valid_type', null);
        switch ($type) {
            case 'range':
                $int = $this->getOption('date_valid_range1', null);

                return $int === null ? null : (int) $int;
            case 'date':
                try {
                    return new \DateTime($this->getOption('date_valid_date1'));
                } catch (\Exception $e) {
                    return;
                }
        }
    }

    public function getDateMax()
    {
        $type = $this->getOption('date_valid_type', null);
        switch ($type) {
            case 'range':
                $int = $this->getOption('date_valid_range2', null);

                return $int === null ? null : (int) $int;
            case 'date':
                try {
                    return new \DateTime($this->getOption('date_valid_date2'));
                } catch (\Exception $e) {
                    return;
                }
        }
    }

    /**
     * Mark as enabled.
     *
     * @param bool $is_enabled
     *
     * @return $this
     */
    public function setIsEnabled($is_enabled)
    {
        $this->setModelField('is_enabled', (bool) $is_enabled);

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
     * Mark as agent field.
     *
     * @param bool $is_agent_field
     *
     * @return $this
     */
    public function setIsAgentField($is_agent_field)
    {
        $this->setModelField('is_agent_field', (bool) $is_agent_field);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAgentField()
    {
        return $this->is_agent_field;
    }

    /**
     * Mark as user enabled.
     *
     * @param bool $is_user_enabled
     *
     * @return $this
     */
    public function setIsUserEnabled($is_user_enabled)
    {
        $this->setModelField('is_user_enabled', (bool) $is_user_enabled);

        return $this;
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
        $this->setModelField('default_value', $default_value);

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        if ($this->isDateType()) {
            $mode = $this->getOption('default_mode', false);
            if ('date' == $mode) {
                $date = new \DateTime($this->default_value);

                return $date->format($this->getDateExpectedFormat());
            } elseif ('current' == $mode) {
                $date = new \DateTime('now');

                return $date->format($this->getDateExpectedFormat());
            } else {
                return;
            }
        }

        return $this->default_value;
    }

    /**
     * Get the phrasename for the handler class. This is just
     * the key of the phrase when showing this fields type.
     * For example, for phrases like "Text box" or "Checkbox" etc listed in the admin interface.
     *
     * @return string
     */
    public function getHandlerClassPhrase()
    {
        $phrase = $this->handler_class;
        $phrase = str_replace('Application\\DeskPRO\\CustomFields\\Handler\\', '', $phrase);
        $phrase = str_replace('\\', '_', $phrase);
        $phrase = "agent.general.field_type_$phrase";
        $phrase = strtolower($phrase);

        return $phrase;
    }

    /**
     * The "short name" for the handler type.
     *
     * @return string
     */
    public function getTypeName()
    {
        $name = $this->handler_class;
        $name = str_replace('Application\\DeskPRO\\CustomFields\\Handler\\', '', $name);
        $name = str_replace('\\', '_', $name);
        $name = strtolower($name);

        return $name;
    }

    /**
     * Gets the widget type. This is the same as the type, except if this is a choice
     * we return the real type of field (e.g., checkbox or radio) based on display options.
     *
     * @return string
     */
    public function getWidgetType()
    {
        $name = $this->getTypeName();

        if ($name === 'choice') {
            if ($this->getOption('expanded')) {
                $name = $this->getOption('multiple') ? 'checkbox' : 'radio';
            } elseif ($this->getOption('multiple')) {
                $name = 'multichoice';
            }
        }

        return $name;
    }

    /**
     * Fetch the search capabiltiies supported by the field.
     *
     * @return array
     */
    public function getSearchCapabilities()
    {
        return $this->getHandler()->getSearchCapabilities();
    }

    /**
     * Get filter capabilties supported by the field.
     *
     * @return array
     */
    public function getFilterCapabilities()
    {
        return $this->getHandler()->getFilterCapabilities();
    }

    /**
     * True if this field is an actual form field (aka not a display field without any input controls).
     *
     * @return bool
     */
    public function isFormField()
    {
        return $this->handler_class != 'Application\DeskPRO\CustomFields\Handler\Display';
    }

    /**
     * @return bool
     */
    public function isChoiceType()
    {
        switch ($this->handler_class) {
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':
                return true;

            default:
                return false;
        }
    }

    /**
     * @return bool
     */
    public function isDateType()
    {
        switch ($this->handler_class) {
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Date':
            case 'Application\\DeskPRO\\CustomFields\\Handler\\DateTime':
                return true;

            default:
                return false;
        }
    }

    /**
     * @return bool
     */
    public function getDateExpectedFormat()
    {
        switch ($this->handler_class) {
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Date':
                return 'Y-m-d';
            case 'Application\\DeskPRO\\CustomFields\\Handler\\DateTime':
                return 'Y-m-d H:i:s';
            default:
                return false;
        }
    }

    public function getType()
    {
        return strtolower(substr($this->handler_class, strrpos($this->handler_class, '\\') + 1));
    }

    /**
     * @param string $property
     *
     * @return string
     */
    public function getPhraseName($property = null, Translate $translate)
    {
        if (!$property) {
            $property = 'title';
        }

        $name = strtolower(\Orb\Util\Util::getBaseClassname($this));

        $phrase_name = 'obj_'.$name.'.'.$this->id.'_'.$property;

        return $phrase_name;
    }

    /**
     * @param string $property
     *
     * @return string
     */
    public function getPhraseDefault($property = null, Translate $translate)
    {
        if ($property == 'description') {
            return $this->description;
        } elseif ($property == 'html') {
            return $this->getRealHtmlOption();
        }

        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = array())
    {
        $data              = parent::toApiData($primary, $deep, $visited);
        $data['type_name'] = $this->getTypeName();

        if ($data['type_name'] == 'choice') {
            $data['choices'] = array();
            $has_children    = $map    = array();

            foreach ($this->children as $c) {
                $map[$c['id']] = $c;
                if ($parent = $c->getOption('parent_id')) {
                    $has_children[$parent] = 1;
                }
            }

            foreach ($this->children as $c) {
                // todo? exclude parents from choice list
//                if (@$has_children[$c['id']]) continue;

                $title = $c['title'];
//                $child = $c;
//                while ($parent = @$map[$child->getOption('parent_id')]) {
//                    $title = $parent['title'] . ' > ' . $title;
//                    $child = $parent;
//                }

                $data['choices'][] = array(
                    'id'            => $c->id,
                    'title'         => $title,
                    'parent_id'     => $c->getOption('parent_id') ?: null,
                    'display_order' => $c->display_order,
                );
            }

            $defaults = array();
            foreach (explode(',', $data['default_value']) as $val) {
                if (strlen($val)) {
                    $defaults[] = (int) $val;
                }
            }
            $data['default_value'] = $defaults;
        }

        if ($data['options']) {
            // Cast "1" to 1 so values are properly encoded to json
            foreach ($data['options'] as &$opt) {
                if (Numbers::isInteger($opt)) {
                    $opt = (int) $opt;
                }
            }
            unset($opt);
        }

        return $data;
    }
}
