<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity;

use DeskPRO\Component\Util\ListUtils;

class CustomData
{
    /**
     * The field ID the value is for.
     *
     * @var int
     */
    public $field = 0;

    /**
     * The value, whatever it is for this type of field.
     *
     * @var mixed
     */
    public $value = null;

    /**
     * @param CustomData[] $customFields1
     * @param CustomData[] $customFields2
     */
    public static function compareFieldArrays(array $customFields1, array $customFields2)
    {
        $changed = [];

        foreach ($customFields1 as $field1) {
            if (!isset($customFields2[$field1->field])) {
                $changed[] = $field1->field;
            } else {
                $field2 = $customFields2[$field1->field];

                if ($field1->value != $field2->value) {
                    if (is_array($field1->value) && is_array($field2->value)) {
                        if (ListUtils::isSame($field1->value, $field2->value)) {
                            $changed[] = $field1->field;
                        }
                    } else {
                        $changed[] = $field1->field;
                    }
                }
            }
        }

        foreach ($customFields2 as $field2) {
            if (!isset($customFields1[$field2->field])) {
                $changed[] = $field2->field;
            }
        }

        return $changed;
    }
}
