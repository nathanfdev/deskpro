<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Domain;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\StateChange\StateChangeRecorder;
use Application\DeskPRO\Tickets\StateChangeRecorder as TicketStateChangeRecorder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Serializer;

/**
 * The basic entitiy class.
 *
 * @Serializer\ExclusionPolicy("ALL")
 */
abstract class BasicDomainObject implements \ArrayAccess, NotifyPropertyChanged
{
    const TOARRAY_NOOP            = 1;
    const TOARRAY_DEEP            = 2;
    const TOARRAY_ONLY_PRIMATIVES = 4;
    const TOARRAY_LOAD_UNLOADED   = 8;

    const MAGIC_OFFSETGET_PROP   = 1;
    const MAGIC_OFFSETGET_GETTER = 2;

    /**
     * Array of listeners.
     *
     * @see addPropertyChangedListener
     *
     * @var array
     */
    private $_listeners = [];

    /**
     * @var array
     */
    private $_custom_callables = [];

    /**
     * @var array
     */
    protected $_magic_offsetget_map = [];

    /**
     * @var StateChangeRecorder
     */
    private $_state_recorder;

    /**
     * @var object
     */
    private $_state_clone;

    /**
     * Special var that should be set during preload in a DataService
     * to help the Doctrine entity persisters from trying to query data we already have.
     *
     * @var null
     */
    public $__dp_is_preloaded_repos = null;

    /**
     * Set values from an array.
     *
     * @param array $values The values to set
     */
    public function fromArray(array $values)
    {
        foreach ($values as $k => $v) {
            $this[$k] = $v;
        }
    }

    /**
     * Get a simple array representation of this entity.
     *
     * @param bool $mode
     *
     * @return array
     */
    public function toArray($mode = self::TOARRAY_NOOP)
    {
        $values = [];

        $only_real = true;

        foreach ($this->getKeys() as $name) {
            if ($only_real) {
                if (!property_exists($this, $name)) {
                    continue;
                }
            }

            $val = $this[$name];

            if (!($mode & self::TOARRAY_LOAD_UNLOADED)) {
                // If a relation isn't loaded then dont access it, or else we'll lazy load it
                if (!is_scalar($val) and !is_array($val) and is_null($val) and ($val instanceof \DateTime) and !\Application\DeskPRO\ORM\Util\Util::isCollectionInitialized($val)) {
                    continue;
                }
            }

            if ($mode & self::TOARRAY_NOOP) {
                $values[$name] = $val;
            } elseif ($mode & self::TOARRAY_ONLY_PRIMATIVES) {
                if (is_scalar($val) or is_array($val) or is_null($val)) {
                    $values[$name] = $val;
                } elseif ($val instanceof \DateTime) {
                    $values[$name] = $val->format('Y-m-d H:i:s');
                }
            } elseif ($mode & self::TOARRAY_DEEP) {
                if (is_object($val) and method_exists($val, 'toArray')) {
                    // If its a DomainObject then we can pass on the mode
                    if ($this->$name instanceof DomainObject) {
                        $val = $val->toArray($mode);
                    // Otherwise it could be some other implementation, so we dont know how to handle it
                    } else {
                        $val = $val->toArray();
                    }
                }
                $values[$name] = $val;
            }
        }

        return $values;
    }

    /**
     * Get an array of keys that can be used on this object to access certain data.
     *
     * @return array
     */
    public function getKeys()
    {
        return $this->getFieldKeys();
    }

    /**
     * Get an array of keys that correspond to real database fields.
     */
    public function getFieldKeys()
    {
        $r     = new \ReflectionObject($this);
        $props = $r->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED);

        $keys = [];
        foreach ($props as $prop) {
            // Skip _props because they arent entity properties
            if ($prop->name[0] === '_') {
                continue;
            }

            $keys[] = $prop->name;
        }

        return $keys;
    }

    /**
     * Checks to see if a particular field on this object exists and is a real database field.
     * By convention, this is any protected property on the object whose name doesnt start with an underscore.
     *
     * @param string $field
     *
     * @return bool
     */
    public function propertyFieldExists($field)
    {
        // If it begins with an undercore, then by convention its not a field
        if ($field[0] === '_') {
            return false;
        }

        try {
            $r    = new \ReflectionObject($this);
            $prop = $r->getProperty($field);
        } catch (\ReflectionException $e) {
            return false;
        }

        if ($prop->isProtected() or $prop->isPrivate()) {
            return true;
        }

        return false;
    }

    /**
     * Get a property of this entity. Same as using $entity[something].
     *
     * @param string $name The property to get
     */
    public function get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * Set the value of a property. Same as using $entity[something].
     *
     * @param string $name  The property to set
     * @param mixed  $value The value to set
     */
    public function set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function __unset($name)
    {
        return $this->offsetUnset($name);
    }

    /**
     * Dynamically implement getX and setX methods.
     */
    public function __call($name, $arguments)
    {
        $name_l = strtolower($name);
        if (isset($this->_custom_callables[$name_l])) {
            return call_user_func($this->_custom_callables[$name_l][0], $this->_custom_callables[$name_l][1], $arguments);
        }

        $name = preg_replace('#([A-Z])#', '_$1', $name);

        $match = null;
        if (!preg_match('#^(get|set|is)_([a-zA-Z0-9_]+)$#', $name, $match)) {
            return $this->_onNotCallable($name, $arguments);
        }

        list(, $type, $prop) = $match;
        $prop                = strtolower($prop);

        // getX
        if ($type == 'is') {
            return $this["is_$prop"];
        } elseif ($type == 'get') {
            return $this[$prop];

        // setX
        } else {
            if (!isset($arguments[0])) {
                $arguments = [null];
            }

            $this[$prop] = $arguments[0];
        }
    }

    protected function _isCustomCallable($name)
    {
        return isset($this->_custom_callables[$name]);
    }

    /**
     * Called when __call finds no suitable attribute to use.
     */
    protected function _onNotCallable($name, $arguments)
    {
        if (isset($GLOBALS['DP_IS_RENDERING_TPL']) && $GLOBALS['DP_IS_RENDERING_TPL']) {
            return "[$name is not defined]";
        }
        throw new \BadMethodCallException("Method `$name` is undefined");
    }

    //###########################################################################
    // ArrayAccess Implementation
    //###########################################################################

    public function offsetExists($offset)
    {
        if (isset($this->_magic_offsetget_map[$offset])) {
            return true;
        }

        if (strpos($offset, 'is_') !== false) {
            $func = str_replace('_', '', $offset);
        } else {
            $func = 'get'.str_replace('_', '', $offset);
        }
        if (method_exists($this, $func)) {
            $this->_magic_offsetget_map[$offset] = [
                self::MAGIC_OFFSETGET_GETTER,
                $func,
            ];

            return true;
        } elseif (property_exists($this, $offset) and $offset[0] != '_') {
            $this->_magic_offsetget_map[$offset] = [
                self::MAGIC_OFFSETGET_PROP,
                $offset,
            ];

            return true;
        } else {
            // Handle _id's
            if (substr($offset, -3) === '_id') {
                $func   = substr($func, 0, -3);
                $offset = substr($offset, 0, -3);
            }

            if (method_exists($this, $func) || isset($this->_custom_callables['get'.strtolower($offset)])) {
                return true;
            } elseif (property_exists($this, $offset) and $offset[0] != '_') {
                return true;
            }

            return false;
        }
    }

    public function offsetSet($offset, $value)
    {
        $func = 'set'.str_replace('_', '', $offset);
        if (method_exists($this, $func) || isset($this->_custom_callables[strtolower($func)])) {
            $this->$func($value);
        } else {
            $old_value     = isset($this[$offset]) ? $this[$offset] : null;
            $this->$offset = $value;
            $this->_onPropertyChanged($offset, $old_value, $value);
        }
    }

    public function offsetGet($offset)
    {
        if (isset($this->_magic_offsetget_map[$offset])) {
            if ($this->_magic_offsetget_map[$offset][0] === self::MAGIC_OFFSETGET_GETTER) {
                $func = $this->_magic_offsetget_map[$offset][1];

                return $this->$func();
            } elseif ($this->_magic_offsetget_map[$offset][0] === self::MAGIC_OFFSETGET_PROP) {
                $prop = $this->_magic_offsetget_map[$offset][1];

                return $this->$prop;
            }
        }

        if (strpos($offset, 'is_') !== false) {
            $func = str_replace('_', '', $offset);
        } else {
            $func = 'get'.str_replace('_', '', $offset);
        }
        if (method_exists($this, $func) || isset($this->_custom_callables[strtolower($func)])) {
            $this->_magic_offsetget_map[$offset] = [
                self::MAGIC_OFFSETGET_GETTER,
                $func,
            ];

            return $this->$func();
        } elseif (property_exists($this, $offset) and $offset[0] != '_') {
            $this->_magic_offsetget_map[$offset] = [
                self::MAGIC_OFFSETGET_PROP,
                $offset,
            ];

            return $this->$offset;
        } else {
            // Handle _id's
            if (substr($offset, -3) === '_id') {
                $func   = substr($func, 0, -3);
                $offset = substr($offset, 0, -3);
            }

            if (method_exists($this, $func)) {
                $obj = $this->$func();
                if ($obj) {
                    return $obj['id'];
                } else {
                    return 0;
                }
            } elseif (property_exists($this, $offset) and $offset[0] != '_') {
                $obj = $this->$offset;
                if ($obj) {
                    return $obj['id'];
                } else {
                    return 0;
                }
            }

            // Always end up calling incase its magic,
            // it'll throw an error if not set anyway
            return $this->$func();
        }
    }

    public function offsetUnset($offset)
    {
        $this->offsetSet($offset, null);
    }

    /**
     * @param PropertyChangedListener $listener
     */
    public function addPropertyChangedListener(PropertyChangedListener $listener)
    {
        if (empty($this->_listeners['property'])) {
            $this->_listeners['property'] = [];
        }

        $this->_listeners['property'][] = $listener;
    }

    /**
     * @param PropertyChangedListener $listener
     */
    public function removePropertyChangedListener(PropertyChangedListener $listener)
    {
        if (empty($this->_listeners['property'])) {
            return;
        }

        foreach ($this->_listeners['property'] as $k => $l) {
            if ($l == $listener) {
                unset($this->_listeners['property'][$k]);
                break;
            }
        }
    }

    /**
     * @param string   $name
     * @param callable $fn
     */
    public function addCustomCallable($name, $fn, $args = null)
    {
        $this->_custom_callables[$name] = [$fn, $args];
    }

    public function ensureDefaultPropertyChangedListener()
    {
        $uow = App::getOrm()->getUnitOfWork();

        if (!empty($this->_listeners['property'])) {
            foreach ($this->_listeners['property'] as $listener) {
                if ($listener === $uow) {
                    return false;
                }
            }
        }

        $this->addPropertyChangedListener($uow);

        return false;
    }

    public function __clone()
    {
        $this->_listeners = [];
    }

    /**
     * @return StateChangeRecorder
     */
    public function getStateChangeRecorder()
    {
        if (!$this->_state_recorder) {
            if ($this instanceof Ticket) {
                $this->_state_recorder = new TicketStateChangeRecorder($this);
            } else {
                $this->_state_recorder = new StateChangeRecorder();
            }

            $this->_state_clone = clone $this;

            // The clone will clone the important bits
            // For collections, we want to create a new collection
            // which will not be affected by add/remove ops elsewhere
            foreach (get_object_vars($this) as $prop => $val) {
                if ($prop[0] == '_' || !is_object($val) || !($val instanceof Collection)) {
                    continue;
                }

                if ($val instanceof PersistentCollection) {
                    // hotfix -- problem filters not updating
                    // https://trello.com/c/h42231s0
                    if ($prop === 'problems' && !$val->isInitialized() && $this instanceof Ticket) {
                        $val->initialize();
                    }
                    if ($val->isInitialized()) {
                        $newVal = new ArrayCollection($val->getSnapshot());
                    } else {
                        $reflection = new \ReflectionClass($val);
                        $newVal     = $reflection->newInstanceWithoutConstructor();

                        foreach ($reflection->getProperties() as $reflectionProperty) {
                            $reflectionProperty->setAccessible(true);

                            if ($reflectionProperty->getName() === 'collection') {
                                $propValue = new ArrayCollection();
                            } else {
                                $propValue = $reflectionProperty->getValue($val);
                            }

                            $reflectionProperty->setValue($newVal, $propValue);
                            $reflectionProperty->setAccessible(false);
                        }
                    }
                } else {
                    $newVal = $val;
                }

                $this->_state_clone->__setPropValue__($prop, $newVal);
            }
        }

        return $this->_state_recorder;
    }

    /**
     * Resets the state change recorder.
     */
    public function resetStateChangeRecorder()
    {
        $this->_state_recorder = null;
        $this->_state_clone    = null;
    }

    /**
     * Returns a clone of this entity which represents the state before changes were made to it.
     *
     * @return object
     */
    public function getOriginalStateClone()
    {
        $this->getStateChangeRecorder();

        return $this->_state_clone;
    }

    /**
     * Notify a prop has changed.
     *
     * @param string $prop
     * @param mixed  $old
     * @param mixed  $new
     * @param bool   $skip_state Do not run through state change recorder (performance opt)
     */
    protected function _onPropertyChanged($prop, $old, $new, $skip_state = false)
    {
        $this->getStateChangeRecorder()->touchField($prop);
        $this->propertyChangedCallback($prop, $old, $new);

        if (!empty($this->_listeners['property'])) {
            foreach ($this->_listeners['property'] as $listener) {
                $listener->propertyChanged($this, $prop, $old, $new);
            }
        }

        if (!$skip_state) {
            if ($prop[0] != '_' && property_exists($this, $prop)) {
                if ($this->$prop instanceof Collection && $new instanceof Collection) {
                    $this->getStateChangeRecorder()
                        ->recordCollection($prop, $new);
                } else {
                    $this->getStateChangeRecorder()
                        ->record($prop, $old, $new);
                }
            }
        }
    }

    protected function propertyChangedCallback($prop, $old, $new)
    {
    }

    public function __getPropValue__($k)
    {
        return $this->$k;
    }
    public function __setPropValue__($k, $v)
    {
        $this->$k = $v;
    }
    public function __hasRunLoad__()
    {
        return true;
    }
}
