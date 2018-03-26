<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity;

class PersonModel
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
     * @var int
     */
    public $language = 0;

    /**
     * @var CustomData[]
     */
    public $custom_fields = [];

    /**
     * @param PersonModel $other
     *
     * @return array
     */
    public function getChangedFields(PersonModel $other)
    {
        $changed = [];

        foreach ([
            'id', 'language',
        ] as $simpleField) {
            if ($this->$simpleField != $other->$simpleField) {
                $changed[] = "ticket.person.$simpleField";
            }
        }

        foreach ([
            'user_groups', 'labels',
        ] as $arrayField) {
            if (!ListUtils::isSame($this->$arrayField, $other->$arrayField)) {
                $changed[] = "ticket.person.$arrayField";
            }
        }

        // Custom fields
        $customFieldChanged = CustomData::compareFieldArrays($this->custom_fields, $other->custom_fields);
        foreach ($customFieldChanged as $fieldId) {
            $changed[] = "ticket.person.field$fieldId";
        }

        return $changed;
    }
}
