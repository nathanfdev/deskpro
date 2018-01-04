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
 * Handles the data json value custom field.
 */
class DataJson extends HandlerAbstract
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
            $decodedValue = null;

            try {
                $decodedValue = is_string($value) ? json_decode($value) : null;
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $decodedValue = null;
                }
            } catch (\Exception $e) {
                $decodedValue = null;
            }

            $data = is_null($decodedValue) ? $value : json_encode($decodedValue);
            return [[$this->field_def->getId(), 'input', $data]] ;
        }

        return [];
    }

    public function getSearchCapabilities()
    {
        return ['is', 'not'];
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
