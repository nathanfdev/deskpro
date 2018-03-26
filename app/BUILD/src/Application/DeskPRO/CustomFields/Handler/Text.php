<?php

namespace Application\DeskPRO\CustomFields\Handler;

use Orb\Util\Strings;

/**
 * Handles the text field.
 */
class Text extends HandlerAbstract
{
    /**
     * @param array $form_data
     * @param null  $default
     *
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

    /**
     * {@inheritdoc}
     */
    public function getDataFromForm(array $formData)
    {
        $value = $this->findValue($formData);
        if (is_array($value)) {
            $value = implode(' ', $value);
        }

        return [
            [$this->field_def['id'], 'input', $value],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validateFormData(array $formData, $context = self::CONTEXT_USER, $contextData = null)
    {
        $valueIfNotPresent = new \stdClass();
        $data              = $this->findValue($formData, $valueIfNotPresent);
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

    /**
     * {@inheritdoc}
     */
    public function getSearchCapabilities()
    {
        return ['is', 'not', 'contains', 'notcontains'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterCapabilities()
    {
        return ['is', 'not'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchType()
    {
        return 'input';
    }
}
