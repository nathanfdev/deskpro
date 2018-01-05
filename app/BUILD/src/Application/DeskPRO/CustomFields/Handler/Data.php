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

namespace Application\DeskPRO\CustomFields\Handler;

/**
 * A field that doesnt have any user-editable form field. It's used by API's or other features to store
 * data attached to things. For example, storing data from a user source on a person.
 */
class Data extends HandlerAbstract
{
    /**
     * @param array $form_data
     * @param null $default
     * @return mixed|null
     */
    private function findValue(array $form_data, $default = null)
    {
        $names = $this->getAllFormFieldNames();
        foreach ($names as $name) {
            if (isset($formData[$name])) {
                return $form_data[$name];
            }
        }
        return $default;
    }

    public function getDataFromForm(array $form_data)
    {
        $valueIfNotPresent = new \stdClass();
        $value = $this->findValue($form_data, $valueIfNotPresent);

        if ($value !== $valueIfNotPresent) {
            return [
                [$this->field_def->getId(), 'input', $value],
            ];
        }

        return [];
    }

    public function getSearchCapabilities()
    {
        return ['is', 'not', 'contains', 'notcontains'];
    }

    public function getFilterCapabilities()
    {
        return ['is', 'not'];
    }

    public function getSearchType()
    {
        return 'input';
    }
}
