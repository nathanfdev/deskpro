<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;

class Skype extends AbstractContactData
{
    /**
     * Apply form data to a contact record.
     *
     * @param array                                           $input
     * @param \Application\DeskPRO\Entity\ContactDataAbstract $contact_record
     */
    public function applyFormData(array $input, ContactDataAbstract $contact_record)
    {
        $contact_record->comment = isset($input['comment']) ? $input['comment'] : '';
        $contact_record->field_1 = $input['username'];
    }

    /**
     * Return an array of values that are useful in a template.
     *
     * @return array
     */
    public function getTemplateVars(ContactDataAbstract $contact_record)
    {
        return [
            'comment'   => $contact_record->comment,
            'username'  => $contact_record->field_1,
            'call_link' => 'skype:' + urlencode($contact_record->field_1),
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
        ];
    }
}
