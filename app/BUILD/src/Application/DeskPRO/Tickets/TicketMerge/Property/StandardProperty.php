<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketMerge\Property;

/**
 * A standard property on a ticket where only one value can exist.
 *
 * The folowing properties are accepted:
 * - department
 * - category
 * - priority
 * - workflow
 * - product
 * - status
 * - urgency
 * - subject
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
            if (!$this->ticket[$this->property]) {
                $do_set = true;
            }
        }

        if ($do_set) {
            $this->ticket[$this->property] = $this->other_ticket[$this->property];

            if ($this->property == 'status' && $this->other_ticket[$this->property] == 'hidden') {
                $this->ticket['hidden_status'] = $this->other_ticket['hidden_status'];
            }
        }
    }
}
