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

use Orb\Util\Strings;

/**
 * Handles the text field.
 */
class Text extends HandlerAbstract
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
            if (!empty($form_data[$name]) || (isset($form_data[$name]) && $form_data[$name] === '0')) {
                return $form_data[$name];
            }
        }
        return $default;
    }

    public function getDataFromForm(array $form_data)
    {
        $value = $this->findValue($form_data);
        if (is_array($value)) {
            $value = implode(' ', $value);
        }

        return [
            [$this->field_def['id'], 'input', $value],
        ];
    }

    public function validateFormData(array $form_data, $context = self::CONTEXT_USER, $context_data = null)
    {
        $valueIfNotPresent = new \stdClass();
        $data = $this->findValue($form_data, $valueIfNotPresent);
        if ($data === $valueIfNotPresent) {
            $data = '';
        }

        if (!is_scalar($data)) {
            return $this->makeErrorArray(['invalid_input']);
        }

        //------------------------------
        // Validate options
        //------------------------------

        $opt_prefix = '';
        if ($context == self::CONTEXT_AGENT) {
            $opt_prefix = 'agent_';
        }

        $options = [];
        foreach (['required', 'min_length', 'max_length', 'regex', 'regex_required'] as $k) {
            $options[$k] = $this->field_def->getOption($opt_prefix.$k);
        }

        if ($options['required']) {
            $len = Strings::utf8_strlen($data);

            if ($options['min_length'] && $len < $options['min_length']) {
                if ($options['min_length'] == 1 || $len === 0) {
                    return $this->makeErrorArray(['required']);
                } else {
                    return $this->makeErrorArray(['min_length']);
                }
            }

            if ($options['max_length'] && $len > $options['max_length']) {
                return $this->makeErrorArray(['max_length']);
            }
        }

        if ($options['regex']) {
            if ($options['regex_required']) {
                if (!Strings::utf8_strlen($data)) {
                    return $this->makeErrorArray(['required']);
                }
            }

            $regex = Strings::getInputRegexPattern($options['regex']);
            if ($regex && $data && !preg_match($regex, $data)) {
                return $this->makeErrorArray(['regex_fail']);
            }
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
