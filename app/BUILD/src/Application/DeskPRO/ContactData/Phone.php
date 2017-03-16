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

class Phone extends AbstractContactData
{
    /**
     * Apply form data to a contact record.
     *
     * @param array               $input
     * @param ContactDataAbstract $contact_record
     */
    public function applyFormData(array $input, ContactDataAbstract $contact_record)
    {
        $contact_record
            ->setComment(isset($input['comment']) ? $input['comment'] : '')
            ->setField1(isset($input['country_calling_code']) ? $input['country_calling_code'] : '')
            ->setField2(isset($input['number']) ? $input['number'] : '')
            ->setField3(isset($input['type']) ? $input['type'] : 'phone');

        // Searchable value without punctuation etc
        $contact_record->setField10(
            preg_replace('#[^0-9a-zA-Z]#', '', $contact_record->getField1().$contact_record->getField2())
        );
    }

    /**
     * Return an array of values that are useful in a template.
     *
     * @param ContactDataAbstract $contact_record
     *
     * @return array
     */
    public function getTemplateVars(ContactDataAbstract $contact_record)
    {
        $vars            = $this->getApiVars($contact_record);
        $vars['comment'] = $contact_record->getComment();

        return $vars;
    }

    /**
     * Return an array of values that are useful to the API.
     *
     * @param ContactDataAbstract $contact_record
     *
     * @return array
     */
    public function getApiVars(ContactDataAbstract $contact_record)
    {
        return [
            'country_calling_code' => $contact_record->getField1(),
            'number'               => $contact_record->getField2(),
            'type'                 => $contact_record->getField3(),
        ];
    }
}
