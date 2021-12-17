<?php

namespace Application\DeskPRO\CustomFields\Handler;

use Orb\Util\Strings;

/**
 * Handles the external id fields.
 */
class ExternalUniqueKey extends HandlerAbstract
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

        $options = [
            'min_length' => $this->field_def->getOption($opt_prefix.'min_length', 1),
        ];

        $len = Strings::utf8_strlen($data);

        if ($len < $options['min_length']) {
            if ($options['min_length'] == 1 || $len === 0) {
                return $this->makeErrorArray(['required']);
            } else {
                return $this->makeErrorArray(['min_length']);
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
