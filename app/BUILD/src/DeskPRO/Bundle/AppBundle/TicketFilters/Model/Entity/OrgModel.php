<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity;

use DeskPRO\Component\Util\ListUtils;

class OrgModel
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string[]
     */
    public $labels = [];

    /**
     * @var int[]
     */
    public $user_groups = [];

    /**
     * @var CustomData[]
     */
    public $custom_fields = [];

    /**
     * @param OrgModel $other
     *
     * @return array
     */
    public function getChangedFields(OrgModel $other)
    {
        $changed = [];

        foreach ([
            'id',
        ] as $simpleField) {
            if ($this->$simpleField != $other->$simpleField) {
                $changed[] = "ticket.organization.$simpleField";
            }
        }

        foreach ([
            'user_groups', 'labels',
        ] as $arrayField) {
            if (!ListUtils::isSame($this->$arrayField, $other->$arrayField)) {
                $changed[] = "ticket.organization.$arrayField";
            }
        }

        // Custom fields
        $customFieldChanged = CustomData::compareFieldArrays($this->custom_fields, $other->custom_fields);
        foreach ($customFieldChanged as $fieldId) {
            $changed[] = "ticket.organization.field$fieldId";
        }

        return $changed;
    }
}
