<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Orb\Util\Strings;

class Facebook extends AbstractContactData
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
        $contact_record->field_1 = $input['profile_url'];

        if (preg_match('#/profile\.php?id=([0-9]+)#', $input['profile_url'], $m)) {
            $contact_record->field_2 = $m[1];
        } elseif (preg_match('#facebook\.com/([a-zA-Z0-9\.\-_]+)#', $input['profile_url'], $m)) {
            $contact_record->field_2 = $m[1];
        } elseif (preg_match('#facebook\.com/people/([a-zA-Z0-9\.\-_]+)#', $input['profile_url'], $m)) {
            $contact_record->field_2 = $m[1];
        } else {
            $contact_record->field_2 = Strings::extractRegexMatch('#(facebook\.com.*?)$#', $input['profile_url'], $m);
        }

        if (!$contact_record->field_2) {
            $contact_record->field_2 = '';
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
            'comment'     => $contact_record->comment,
            'profile_url' => $contact_record->field_1,
            'display'     => $contact_record->field_2 ?: $contact_record->field_1,
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
            'profile_url' => $contact_record->field_1,
            'display'     => $contact_record->field_2 ?: $contact_record->field_1,
        ];
    }
}
