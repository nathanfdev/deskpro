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

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;

/**
 * Class Url.
 */
class Url extends HandlerAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getWidgetName()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function validateFormData(array $form_data, $context = self::CONTEXT_USER, $context_data = null)
    {
        $data = $this->findValue($form_data);
        if (!$data) {
            $data = '';
        }

        if (!is_scalar($data)) {
            return $this->makeErrorArray(['invalid_input']);
        }

        $optPrefix = '';
        if ($context == self::CONTEXT_AGENT) {
            $optPrefix = 'agent_';
        }

        $options = [];
        foreach (['required'] as $k) {
            $options[$k] = $this->field_def->getOption($optPrefix.$k);
        }

        if ($options['required']) {
            if (!$data) {
                return $this->makeErrorArray(['required']);
            }
        }

        $errors = App::$container->get('validator')->validate($data, [
            new AppAssert\Url([
                'allowFile' => $this->field_def->getOption('allow_file'),
            ]),
        ]);

        if ($errors->count() > 0) {
            return $this->makeErrorArray(['invalid_input']);
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
}
