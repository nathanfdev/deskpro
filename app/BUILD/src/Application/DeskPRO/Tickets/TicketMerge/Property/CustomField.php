<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketMerge\Property;

use Application\DeskPRO\Entity\CustomDataTicket;
use Application\DeskPRO\Entity\CustomDefTicket;

/**
 * Merges custom fields.
 */
class CustomField extends PropertyAbstract
{
    /**
     * @var \Application\DeskPRO\Entity\CustomDefTicket
     */
    protected $field;

    /**
     * Data that will potentially be lost.
     *
     * @var mixed|null
     */
    public $lost = null;

    /**
     * @param CustomDefTicket $field
     */
    public function setField(CustomDefTicket $field)
    {
        $this->field = $field;
    }

    public function merge()
    {
        if ($this->strategy == self::STRATEGY_LEFT) {
            return;
        }

        // No children means its a simple field (text input etc)
        if (!count($this->field->getChildren())) {
            if ($this->strategy == self::STRATEGY_RIGHT) {
                $other_exist = $this->other_ticket->getCustomDataForField($this->field);
                if ($other_exist) {
                    $this->addCustomData($other_exist);
                }
            } elseif ($this->strategy == self::STRATEGY_COMBINE) {
                $exist       = $this->ticket->getCustomDataForField($this->field->getId());
                $other_exist = $this->other_ticket->getCustomDataForField($this->field->getId());
                if ($exist && $exist->getData()) {
                    if ($other_exist && $exist->getData() != $other_exist->getData()) {
                        $this->lost = $other_exist->getData();
                    }

                    return;
                }

                if ($other_exist) {
                    $this->addCustomData($other_exist);
                }
            }

        // Children means we can potentially merge selections
        } else {
            $multiple = $this->field->getOption('multiple');
            $hasValue = false;
            foreach ($this->field->getChildren() as $child) {
                if ($this->ticket->getCustomDataForField($child)) {
                    $hasValue = true;
                }
            }

            if ($this->strategy == self::STRATEGY_COMBINE) {
                foreach ($this->field->getChildren() as $child) {
                    // Ignore if left already has a value
                    $exist       = $this->ticket->getCustomDataForField($child);
                    $other_exist = $this->other_ticket->getCustomDataForField($child);
                    if ($exist) {
                        if ($other_exist && $exist->getData() != $other_exist->getData()) {
                            $this->lost = $other_exist->getData();
                        }
                        continue;
                    }

                    if ($other_exist) {
                        if (!$multiple && $hasValue) {
                            // already have a value for this field, so losing the other
                            $this->lost = $other_exist->getData();
                            continue;
                        }

                        $this->addCustomData($other_exist);
                    }
                }
            } elseif ($this->strategy == self::STRATEGY_RIGHT) {
                // Take right ones over left ones
                foreach ($this->field->getChildren() as $child) {
                    $other_exist = $this->other_ticket->getCustomDataForField($child);
                    if ($other_exist) {
                        $this->addCustomData($other_exist);
                    }
                }
            }
        }
    }

    /**
     * @param CustomDataTicket $data
     */
    protected function addCustomData(CustomDataTicket $data)
    {
        /** @var CustomDefTicket $field */
        $field   = $data->getField();
        $oldData = $this->ticket->getCustomDataForField($field);

        $newData = $oldData ?: new CustomDataTicket();
        $newData->setValue($data->getValue());
        $newData->setInput($data->getInput());
        $newData->setField($data->getField());
        $newData->setRootField($data->getRootField());
        $newData->setTicket($this->ticket);

        $this->ticket->addCustomData($newData);
    }
}
