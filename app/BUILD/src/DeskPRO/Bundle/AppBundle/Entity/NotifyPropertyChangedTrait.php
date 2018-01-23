<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
        } elseif (is_object($value) && $value instanceof EntityInterface && is_object($old) && $old instanceof EntityInterface) {
            if ($value->getId() === $old->getId()) {
                return;
            }
        }

        $this->$field = $value;

        foreach ($this->_listeners as $listener) {
            $listener->propertyChanged($this, $field, $old, $value);
        }
    }
}
