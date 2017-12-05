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
            if (!isset($customFields2[$field1->id])) {
                $changed[] = $field1->id;
            } else {
                $field2 = $customFields2[$field1->id];

                if ($field1->value != $field2->value) {
                    if (is_array($field1->value) && is_array($field2->value)) {
                        if (ListUtils::isSame($field1->value, $field2->value)) {
                            $changed[] = $field1->id;
                        }
                    } else {
                        $changed[] = $field1->id;
                    }
                }
            }
        }

        foreach ($customFields2 as $field2) {
            if (!isset($customFields1[$field2->id])) {
                $changed[] = $field2->id;
            }
        }

        return $changed;
    }
}
