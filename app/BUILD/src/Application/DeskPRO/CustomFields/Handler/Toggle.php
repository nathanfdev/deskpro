<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace Application\DeskPRO\CustomFields\Handler;

/**
 * Handles the toggle field.
 */
class Toggle extends HandlerAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getWidgetName()
    {
        return 'checkbox';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormField($data = null)
    {
        if ($data and !empty($data['value'])) {
            $data['value'] = true;
        }

        $data['value'] = (bool) @$data['value'];

        return parent::getFormField($data);
    }

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
            if (!empty($form_data[$name])) {
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
        if (!empty($value)) {
            return [
                [$this->field_def['id'], 'value', 1],
            ];
        }

        return [];
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
        if ('required' === $this->field_def->getOption($opt_prefix.'validation_type')) {
            $options['required'] = true;
        }

        if (@$options['required']) {
            if (!$data || $data != '1') {
                return $this->makeErrorArray(['required']);
            }
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCapabilities()
    {
        return ['is', 'not'];
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
        return 'value';
    }
}
