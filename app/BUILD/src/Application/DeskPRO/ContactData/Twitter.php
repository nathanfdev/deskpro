<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;

class Twitter extends AbstractContactData
{
    /**
     * Apply form data to a contact record.
     *
     * @param array                                           $input
     * @param \Application\DeskPRO\Entity\ContactDataAbstract $contact_record
     */
    public function applyFormData(array $input, ContactDataAbstract $contact_record)
    {
        $old_name = $contact_record->field_1;

        $contact_record->comment = isset($input['comment']) ? $input['comment'] : '';
        $contact_record->field_1 = $input['username'];
        $contact_record->field_2 = isset($input['display_feed']) && $input['display_feed'] ? 1 : 0;

        if ($contact_record instanceof \Application\DeskPRO\Entity\PersonContactData
            || $contact_record instanceof \Application\DeskPRO\Entity\OrganizationContactData
        ) {
            if ($old_name != $contact_record->field_1) {
                // changing the name - not verified
                $contact_record->field_3  = '';
                $contact_record->field_10 = '';
            }
        }
    }

    /**
     * Return an array of values that are useful in a template.
     *
     * @return array
     */
    public function getTemplateVars(ContactDataAbstract $contact_record)
    {
        return [
            'comment'      => $contact_record->comment,
            'username'     => $contact_record->field_1,
            'profile_url'  => 'http://twitter.com/'.$contact_record->field_1,
            'display_feed' => $contact_record->field_2,
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
