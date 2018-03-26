<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PersonMerge\Property;

/**
 * A standard property on a person where only one value can exist.
 */
class StandardProperty extends PropertyAbstract
{
    /**
     * @var string
     */
    protected $property;

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function merge()
    {
        $do_set = false;

        if ($this->strategy == self::STRATEGY_RIGHT) {
            $do_set = true;
        } elseif ($this->strategy == self::STRATEGY_COMBINE) {
            if (!$this->person[$this->property]) {
                $do_set = true;
            }
        }

        if ($do_set) {
            $this->person[$this->property] = $this->other_person[$this->property];
        }
    }
}
