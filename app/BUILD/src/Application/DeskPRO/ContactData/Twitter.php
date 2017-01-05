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
