<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\PropertyChangedListener;

trait NotifyPropertyChangedTrait
{
    /**
     * @var PropertyChangedListener[]
     */
    private $_listeners = [];

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

    public function addPropertyChangedListener(PropertyChangedListener $listener)
    {
        $this->_listeners[] = $listener;
    }

    protected function setModelField($field, $value)
    {
        $old = null;
        if (property_exists($this, $field)) {
            $old = $this->$field;
        }

        // Detect fields that did not change
        if (is_null($value) && is_null($old)) {
            return;
        } elseif (is_scalar($value)) {
            if (is_numeric($value) && is_numeric($old)) {
                if ($value == $old) {
                    return;
                }
            } else {
                if ($value === $old) {
                    return;
                }
            }
        } elseif ($value instanceof \DateTime) {
            if ($old instanceof \DateTime && $value->getTimestamp() == $old->getTimestamp()) {
                return;
            }
        } elseif (is_object($value) && isset($value->id) && is_object($old) && isset($old->id)) {
            if ($value->id == $old->id) {
                return;
            }
        }

        $this->$field = $value;

        foreach ($this->_listeners as $listener) {
            $listener->propertyChanged($this, $field, $old, $value);
        }
    }
}
