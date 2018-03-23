<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\Handler;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\ObjectAlias;
use Doctrine\Common\Collections\ArrayCollection;
use Orb\Util\Numbers;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A custom field definition.
 *
 * @property int                                 $display_order
 * @property CustomDefAbstract|null              $parent
 * @property CustomDefAbstract[]|ArrayCollection $children
 *
 * @method setParent(CustomDefAbstract $parent)
 */
class CustomDefAbstract extends \Application\DeskPRO\Domain\DomainObject implements HasPhraseName
{
    const HANDLER_CLASS_TEXT     = Handler\Text::class;
    const HANDLER_CLASS_TEXTAREA = Handler\Textarea::class;
    const HANDLER_CLASS_CHOICE   = Handler\Choice::class;
    const HANDLER_CLASS_TOGGLE   = Handler\Toggle::class;
    const HANDLER_CLASS_DATE     = Handler\Date::class;
    const HANDLER_CLASS_DATETIME = Handler\DateTime::class;
    const HANDLER_CLASS_DISPLAY  = Handler\Display::class;
    const HANDLER_CLASS_HIDDEN   = Handler\Hidden::class;
    const HANDLER_CLASS_DATA     = Handler\Data::class;
    const HANDLER_CLASS_DATAJSON = Handler\DataJson::class;
    const HANDLER_CLASS_DATALIST = Handler\DataList::class;
    const HANDLER_CLASS_URL      = Handler\Url::class;
    const HANDLER_CLASS_CURRENCY = Handler\Currency::class;

    const TYPE_TEXT      = 'text';
    const TYPE_TEXTAREA  = 'textarea';
    const TYPE_CHOICE    = 'choice';
    const TYPE_TOGGLE    = 'toggle';
    const TYPE_DATE      = 'date';
    const TYPE_DATETIME  = 'datetime';
    const TYPE_DISPLAY   = 'display';
    const TYPE_HIDDEN    = 'hidden';
    const TYPE_DATA      = 'data';
    const TYPE_DATA_JSON = 'datajson';
    const TYPE_DATA_LIST = 'datalist';
    const TYPE_URL       = 'url';
    const TYPE_CURRENCY  = 'currency';

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
     *
     * @Assert\NotBlank()
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
     *
     * @var array
     */
    protected $options = [];

    /**
     * Can the field be viewed by the user?
     *
     * @var bool
     */
    protected $is_user_enabled = true;

    /**
     * True if field is enabled.
     *
     * @var bool
     */
    protected $is_enabled = true;

    /**
     * Obviously it is field`s display order.
     *
     * @var int
     */
    protected $display_order = 0;

    /**
     * Default field value.
     *
     * @var string
     */
    protected $default_value = null;

    /**
     * Is this field associated with agents only.
     *
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
     * @return ObjectAlias\ObjectAliasInterface[]|null
     */
    public function getAliases()
    {
        return [];
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

    public function setDisplayOreder($int)
    {
        $this->setModelField('display_order', $int);

        return $this;
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
     * @param Language|int $language
     *
     * @return string
     */
    public function getTitle($language = null)
    {
        return App::getTranslator()->getPhraseObject($this, 'title', $language);
    }

    /**
     * @return string
     */
    public function getRawTitle()
    {
        return $this->title;
    }

    /**
     * @param Language|int $language
     *
     * @return string
     */
    public function getDescription($language = null)
    {
        return App::getTranslator()->getPhraseObject($this, 'description', $language);
    }

    /**
     * @return string
     */
    public function getRawDescription()
    {
        return $this->description;
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
     * @return CustomDefAbstract[]|ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param CustomDefAbstract $parentChoice
     *
     * @return ArrayCollection
     */
    public function getSubChoices(CustomDefAbstract $parentChoice)
    {
        $subChoices = new ArrayCollection();
        if ($this->children->contains($parentChoice)) {
            foreach ($this->children as $child) {
                if ((int) $child->getOption('parent_id') === $parentChoice->getId()) {
                    $subChoices->add($child);
                }
            }
        }

        return $subChoices;
    }

    /**
     * @return bool
     */
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
     * @return array
     */
    public function getChoices()
    {
        $map      = [];
        $children = $this->getChildren();
        foreach ($children as $c) {
            $pid = (int) $c->getOption('parent_id', 0);
            if (!isset($map[$pid])) {
                $map[$pid] = [];
            }

            $map[$pid][$c->getId()] = $c;
        }

        $iter = function ($parent_id, $depth = 0) use ($map, &$iter) {
            if (empty($map[$parent_id])) {
                return [];
            }

            $level_choices = [];
            /** @var CustomDefAbstract $c */
            foreach ($map[$parent_id] as $c) {
                $subs = $iter($c->getId(), $depth + 1);
                $row  = [
                    'id'            => $c->getId(),
                    'title'         => $c->getTitle(),
                    'is_selectable' => empty($subs),
                ];

                if ($subs) {
                    $row['children'] = $subs;
                }

                $level_choices[] = $row;
            }

            return $level_choices;
        };

        return $iter(0);
    }

    /**
     * @return int[]
     */
    public function getChoiceIds()
    {
        $ids      = [];
        $iterator = function (CustomDefAbstract $custom_def) use (&$ids, &$iterator) {
            $children = $custom_def->getChildren();
            foreach ($children as $child) {
                if (count($child->getChildren()) > 0) {
                    $iterator($child);
                } else {
                    $ids[] = $child->getId();
                }
            }
        };

        $iterator($this);

        return $ids;
    }

    /**
     * Add a child to this field.
     *
     * @param CustomDefAbstract $def
     *
     * @return $this
     */
    public function addChild(CustomDefAbstract $def)
    {
        if (!$this->children->contains($def)) {
            $this->children->add($def);
            $this->_onPropertyChanged('children', $this->children, $this->children);
        }

        if ($def->getParent() !== $this) {
            $def['parent'] = $this;
        }

        return $this;
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
     * @return array
     */
    public function getAllChildTitles()
    {
        $titles = [];
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
     * @return mixed
     */
    public function getCustomDataClass()
    {
        return str_replace('Def', 'Data', get_class($this));
    }

    /**
     * @return CustomDataAbstract
     */
    public function createCustomData()
    {
        /* @var CustomDataAbstract $customData */
        $className  = $this->getCustomDataClass();
        $customData = new $className();
        $customData->setRootField($this);

        if (!$this->isChoiceType()) {
            $customData->setField($this);
        }

        return $customData;
    }

    /**
     * Get the value of an option, or a default value if none is set.
     *
     * @param      $name
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

    /**
     * @param bool $isAgent
     *
     * @return bool
     */
    public function isRegexRequired($isAgent = false)
    {
        $option_name = ($isAgent ? 'agent_' : '').'regex_required';

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

    /**
     * @return string|void
     */
    public function getDateMinFormat()
    {
        $dateMin = $this->getDateMin();

        if ($dateMin instanceof \DateTime) {
            return $dateMin->format('c');
        } elseif (is_int($dateMin)) {
            return (new \DateTime('-'.$dateMin.' days'))->format('c');
        }

        return;
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
     * @return string|void
     */
    public function getDateMaxFormat()
    {
        $dateMax = $this->getDateMax();

        if ($dateMax instanceof  \DateTime) {
            return $dateMax->format('c');
        } elseif (is_int($dateMax)) {
            return (new \DateTime('+'.$dateMax.' days'))->format('c');
        }

        return;
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
    public function getStringDefaultValue()
    {
        $defaultValue = $this->getDefaultValue();

        if (is_array($defaultValue)) {
            $defaultValue = implode(',', $defaultValue);
        }
        if (!$defaultValue) {
            $defaultValue = '';
        }

        return $defaultValue;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        if ($this->isDateType()) {
            $mode = $this->getOption('default_mode', false);
            if ('date' == $mode) {
                try {
                    $date = new \DateTime($this->default_value);
                } catch (\Exception $e) {
                    return;
                }

                return $date->format($this->getDateExpectedFormat());
            } elseif ('current' == $mode) {
                $date = new \DateTime('now');

                return $date->format($this->getDateExpectedFormat());
            } else {
                return;
            }
        } elseif ($this->isMulti()) {
            if ($this->default_value) {
                $ids = explode(',', $this->default_value);
                $ids = array_map(function ($id) {
                    return (int) $id;
                }, $ids);

                return $ids;
            }

            return [];
        } elseif ($this->isChoiceType()) {
            return $this->default_value ? (int) $this->default_value : null;
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
     * @param string $widgetType
     *
     * @return $this
     */
    public function setWidgetType($widgetType)
    {
        switch ($widgetType) {
            case self::TYPE_TEXT:
                $this->setHandlerClass(self::HANDLER_CLASS_TEXT);
                break;
            case self::TYPE_TEXTAREA:
                $this->setHandlerClass(self::HANDLER_CLASS_TEXTAREA);
                break;
            case self::TYPE_TOGGLE:
                $this->setHandlerClass(self::HANDLER_CLASS_TOGGLE);
                break;
            case self::TYPE_HIDDEN:
                $this->setHandlerClass(self::HANDLER_CLASS_HIDDEN);
                break;
            case self::TYPE_DISPLAY:
                $this->setHandlerClass(self::HANDLER_CLASS_DISPLAY);
                break;
            case self::TYPE_DATE:
                $this->setHandlerClass(self::HANDLER_CLASS_DATE);
                break;
            case self::TYPE_DATETIME:
                $this->setHandlerClass(self::HANDLER_CLASS_DATETIME);
                break;
            case self::TYPE_CHOICE:
                $this->setHandlerClass(self::HANDLER_CLASS_CHOICE);
                break;

            // extended choice types
            case 'multichoice':
                $this->setHandlerClass(self::HANDLER_CLASS_CHOICE);
                $this->setOption('multiple', true);
                $this->setOption('expanded', false);
                break;
            case 'checkbox':
                $this->setHandlerClass(self::HANDLER_CLASS_CHOICE);
                $this->setOption('multiple', true);
                $this->setOption('expanded', true);
                break;
            case 'radio':
                $this->setHandlerClass(self::HANDLER_CLASS_CHOICE);
                $this->setOption('expanded', true);
                $this->setOption('multiple', false);
                break;
            default:
                throw new \Exception("Unknown widget type `$widgetType`");
        }

        return $this;
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
        return $this->handler_class === self::HANDLER_CLASS_CHOICE;
    }

    /**
     * True if there can be multiple values for this type.
     *
     * @return bool
     */
    public function isMulti()
    {
        return $this->isChoiceType() && $this->getOption('multiple');
    }

    /**
     * @return bool
     */
    public function isRadio()
    {
        return $this->isChoiceType() && !$this->getOption('multiple') && $this->getOption('expanded');
    }

    /**
     * @return bool
     */
    public function isDateType()
    {
        return in_array($this->handler_class, [self::HANDLER_CLASS_DATE, self::HANDLER_CLASS_DATETIME], true);
    }

    /**
     * @return bool
     */
    public function isDisplayType()
    {
        return $this->handler_class === self::HANDLER_CLASS_DISPLAY;
    }

    /**
     * @return bool
     */
    public function isDataJsonType()
    {
        return $this->handler_class === self::HANDLER_CLASS_DATAJSON;
    }

    /**
     * @return bool
     */
    public function isDataListType()
    {
        return $this->handler_class === self::HANDLER_CLASS_DATALIST;
    }

    /**
     * @return bool
     */
    public function isCurrencyType()
    {
        return $this->handler_class === self::HANDLER_CLASS_CURRENCY;
    }

    /**
     * @return bool
     */
    public function getDateExpectedFormat()
    {
        switch ($this->handler_class) {
            case self::HANDLER_CLASS_DATE:
                return 'Y-m-d';
            case self::HANDLER_CLASS_DATETIME:
                return 'Y-m-d H:i:s';
            default:
                return false;
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return strtolower(substr($this->handler_class, strrpos($this->handler_class, '\\') + 1));
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseName($property)
    {
        if (!$property) {
            $property = 'title';
        }

        return $this->getPropertyPhraseName($property);
    }

    /**
     * @param string $property
     *
     * @return string
     */
    public function getPropertyPhraseName($property)
    {
        $name       = strtolower(\Orb\Util\Util::getBaseClassname($this));
        $phraseName = 'obj_'.$name.'.'.$this->id.'_'.$property;

        return $phraseName;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseDefault($property, Translate $translate)
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
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data              = parent::toApiData($primary, $deep, $visited);
        $data['type_name'] = $this->getTypeName();

        if ($data['type_name'] == 'choice') {
            $data['choices'] = [];
            $has_children    = $map    = [];

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

                $data['choices'][] = [
                    'id'            => $c->id,
                    'title'         => $title,
                    'parent_id'     => $c->getOption('parent_id') ?: null,
                    'display_order' => $c->display_order,
                    'disabled'      => isset($has_children[$c->id]),
                ];
            }
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
