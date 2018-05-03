<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Domain;

use Application\DeskPRO\Entity;

/**
 * This change tracker is meant to listen to changes on an object, and then after all changes
 * were committed, it calls listeners.
 *
 * So this is just an intermediary listener that records changes all, and then notifies other listeners
 * at the end. This allows those listeners to run deep inspections on all changes, rather than just know about
 * changes as they happen.
 */
abstract class ChangeTracker implements \Doctrine\Common\PropertyChangedListener
{
    /** @var DomainObject */
    protected $entity;
    /** @var array */
    protected $changes = [];
    /** @var array */
    public $extra = [];

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get the entity.
     */
    public function getEntity()
    {
        return $this->entity;
    }

    public function propertyChanged($sender, $prop, $old_val, $new_val)
    {
        $this->recordPropertyChanged($prop, $old_val, $new_val);
    }

    /**
     * Log a property change.
     *
     * @param  $prop
     * @param  $old_val
     * @param  $new_val
     */
    public function recordPropertyChanged($prop, $old_val, $new_val)
    {
        // Detect fields that did not change
        if (is_null($new_val) && is_null($old_val)) {
            return;
        } elseif (is_scalar($new_val)) {
            if ($new_val == $old_val) {
                return;
            }
        } elseif ($new_val instanceof \DateTime) {
            if ($old_val instanceof \DateTime && $new_val->getTimestamp() == $old_val->getTimestamp()) {
                return;
            }
        } elseif (is_object($new_val) && isset($new_val->id) && is_object($old_val)) {
            if ($new_val->id == $old_val->id) {
                return;
            }
        }

        // It may have been reset more than once before being saved
        if (isset($this->changes[$prop])) {
            $old_val = $this->changes[$prop]['old'];
        }

        $this->changes[$prop] = $this->getChangeData($prop, $old_val, $new_val);
    }

    /**
     * Log a property change where the value is multiple, such as additions to a collection.
     *
     * @param  $prop
     * @param  $old_val
     * @param  $new_val
     */
    public function recordMultiPropertyChanged($prop, $old_val, $new_val)
    {
        if (!isset($this->changes[$prop])) {
            $this->changes[$prop] = [];
        }

        $this->changes[$prop][] = $this->getChangeData($prop, $old_val, $new_val);
    }

    /**
     * @param $prop
     * @param $old_val
     * @param $new_val
     *
     * @return array
     */
    public function getChangeData($prop, $old_val, $new_val)
    {
        return ['old' => $old_val, 'new' => $new_val];
    }

    /**
     * Get details of a property change.
     *
     * @param  $prop
     *
     * @return array|null
     */
    public function getChangedProperty($prop)
    {
        return isset($this->changes[$prop]) ? $this->changes[$prop] : null;
    }

    /**
     * Get array of all property changes.
     *
     * @return array
     */
    public function getAllChangedProperties()
    {
        return $this->changes;
    }

    /**
     * Get the names of all changed properties.
     *
     * @return array
     */
    public function getAllChangedPropertyNames()
    {
        return array_keys($this->changes);
    }

    /**
     * Check if a specific property is changed.
     *
     * @param  $prop
     *
     * @return bool
     */
    public function isPropertyChanged($prop)
    {
        return isset($this->changes[$prop]);
    }

    /**
     * Record some extra data about a ticket event that listeners might be interested in.
     *
     * @param  $key
     * @param  $value
     */
    public function recordExtra($key, $value)
    {
        $this->extra[$key] = $value;
    }

    /**
     * @param $key
     * @param $value
     */
    public function recordExtraMulti($key, $value)
    {
        if (!isset($this->extra[$key])) {
            $this->extra[$key] = [];
        }

        $this->extra[$key][] = $value;
    }

    /**
     * Get extra data.
     *
     * @param  $key
     *
     * @return array|null
     */
    public function getExtra($key)
    {
        return isset($this->extra[$key]) ? $this->extra[$key] : null;
    }

    /**
     * Get an array of all registered extra data.
     *
     * @return array
     */
    public function getAllExtra()
    {
        return $this->extra;
    }

    /**
     * Check if some extra data item is set.
     *
     * @param  $key
     *
     * @return bool
     */
    public function isExtraSet($key)
    {
        return isset($this->extra[$key]);
    }

    /**
     * Notify all listeners that changes to the entity have been committed.
     */
    abstract public function done();
}
