<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;

class Website extends AbstractContactData
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

        $input['url'] = isset($input['url']) ? $input['url'] : '';

        if (!preg_match('#^(.*?)://#', $input['url'])) {
            $input['url'] = 'http://'.$input['url'];
        }

        $contact_record->field_1 = $input['url'];
    }

    /**
     * Return an array of values that are useful in a template.
     *
     * @return array
     */
    public function getTemplateVars(ContactDataAbstract $contact_record)
    {
        $display = preg_replace('#^https?://#', '', $contact_record->field_1);
        $display = preg_replace('#^www\.#', '', $display);
        $display = preg_replace('#/$#', '', $display);

        return [
            'comment' => $contact_record->comment,
            'url'     => $contact_record->field_1,
            'display' => $display,
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
            'url' => $contact_record->field_1,
        ];
    }
}
