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
 * @category People
 */

namespace Application\DeskPRO\People\PersonMerge\Property;

use Application\DeskPRO\Entity\CustomDataPerson;
use Application\DeskPRO\Entity\CustomDefPerson;

/**
 * Merges custom fields.
 */
class CustomField extends PropertyAbstract
{
    /**
     * @var \Application\DeskPRO\Entity\CustomDefPerson
     */
    protected $field;

    public function setField(CustomDefPerson $field)
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
                $otherExist = $this->other_person->getCustomDataForField($this->field->getId());
                if ($otherExist) {
                    $exist = $this->person->getCustomDataForField($this->field->getId());
                    if ($exist) {
                        $exist->setInput($otherExist->getInput());
                    } else {
                        $this->_addCustomData($otherExist);
                    }
                }
            } elseif ($this->strategy == self::STRATEGY_COMBINE) {
                $exist = $this->person->getCustomDataForField($this->field->getId());
                if ($exist && $exist->getInput() !== '') {
                    return;
                }

                $otherExist = $this->other_person->getCustomDataForField($this->field->getId());
                if ($otherExist) {
                    if ($exist) {
                        $exist->setInput($otherExist->getInput());
                    } else {
                        $this->_addCustomData($otherExist);
                    }
                }
            }

        // Children means we can potentially merge selections
        } else {
            $multiple      = $this->field->getOption('multiple');
            $hasValue      = false;
            $hasOtherValue = false;
            foreach ($this->field->getChildren() as $child) {
                if ($this->person->getCustomDataForField($child)) {
                    $hasValue = true;
                }
                if ($this->other_person->getCustomDataForField($child)) {
                    $hasOtherValue = true;
                }
            }

            if ($this->strategy == self::STRATEGY_COMBINE) {
                foreach ($this->field->getChildren() as $child) {
                    // Ignore if left already has a value
                    $exist      = $this->person->getCustomDataForField($child);
                    $otherExist = $this->other_person->getCustomDataForField($child);
                    if ($exist) {
                        continue;
                    }

                    if ($otherExist) {
                        if (!$multiple && $hasValue) {
                            // already have a value for this field, so losing the other
                            continue;
                        }

                        $this->_addCustomData($otherExist);
                    }
                }
            } elseif ($this->strategy == self::STRATEGY_RIGHT) {
                // Take right ones over left ones
                foreach ($this->field->getChildren() as $child) {
                    $exist      = $this->person->getCustomDataForField($child);
                    $otherExist = $this->other_person->getCustomDataForField($child);
                    if ($otherExist) {
                        if (!$exist) {
                            $this->_addCustomData($otherExist);
                        }
                    } else {
                        if ($exist && $hasOtherValue) {
                            $this->person->removeCustomDataForField($child);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param CustomDataPerson $data
     */
    protected function _addCustomData(CustomDataPerson $data)
    {
        $newData = new CustomDataPerson();
        $newData->setValue($data->getValue());
        $newData->setInput($data->getInput());
        $newData->setField($data->getField());
        $newData->setRootField($data->getRootField());
        $newData->setPerson($this->person);

        $this->person->addCustomData($newData);
    }
}
