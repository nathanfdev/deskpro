<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;

class InstantMessage extends AbstractContactData
{
    /**
     * Apply form data to a contact record.
     *
     * @param array                                           $input
     * @param \Application\DeskPRO\Entity\ContactDataAbstract $contact_record
     */
    public function applyFormData(array $input, ContactDataAbstract $contact_record)
    {
        $contact_record->field_1 = $input['username'];
        $contact_record->field_2 = $input['service'];
        $contact_record->comment = isset($input['comment']) ? $input['comment'] : '';
    }

    /**
     * Return an array of values that are useful in a template.
     *
     * @return array
     */
    public function getTemplateVars(ContactDataAbstract $contact_record)
    {
        return [
            'username' => $contact_record->field_1,
            'service'  => $contact_record->field_2,
            'comment'  => $contact_record->comment,
        ];
    }

    /**
     * Return an array of values that are useful to the API.
     *
     * @return array
     */
    public function getApiVars(ContactDataAbstract $contact_record)
    {
        return [
            'username' => $contact_record->field_1,
            'service'  => $contact_record->field_2,
        ];
    }
}
