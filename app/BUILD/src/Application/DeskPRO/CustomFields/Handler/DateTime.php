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
use Application\DeskPRO\Form\Type\CriteriaFilterField\DateTimeType;
use Orb\Util\Dates;

/**
 * Handles the datetime field.
 */
class DateTime extends Date
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
        if (!$value) {
            return [];
        }

        $person = App::getCurrentPerson();
        $date   = \DateTime::createFromFormat($this->getFormat(), $value, $person ? $person->getDateTimezone() : new \DateTimeZone('UTC'));
        if (!$date) {
            return [];
        }

        $date = Dates::convertToUtcDateTime($date);

        return [
            [$this->field_def['id'], 'value', $date->getTimestamp()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormat()
    {
        return 'Y-m-d H:i';
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

        if ($data && !is_scalar($data)) {
            return $this->makeErrorArray(['date_invalid']);
        }

        //------------------------------
        // Validate options
        //------------------------------

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

        if ($data) {
            try {
                $date = new \DateTime('@'.$data);
            } catch (\Exception $e) {
                try {
                    $date = new \DateTime($data);
                } catch (\Exception $e) {
                    $date = null;
                }
            }

            if ($date) {
                try {
                    $adminTz = new \DateTimeZone($this->field_def->getOption('date_valid_timezone'));
                } catch (\Exception $e) {
                    $adminTz = App::getCurrentPerson()->getDateTimezone();
                }

                $date->setTimezone($adminTz);
            } else {
                return $this->makeErrorArray(['date_invalid']);
            }
        } else {
            return [];
        }

        //------------------------------
        // Validate ranges
        //------------------------------

        $dow = intval($date->format('N')) - 1;

        // Days of week
        if ($validDow = $this->field_def->getOption('date_valid_dow')) {
            if (!in_array($dow, $validDow)) {
                return $this->makeErrorArray(['date_invalid_dow']);
            }
        }

        // Specific date ranges
        if ($this->field_def->getOption('date_valid_type') == 'date') {
            $d1 = $this->field_def->getOption('date_valid_date1');
            $d2 = $this->field_def->getOption('date_valid_date2');

            if ($d1) {
                $d1 = \DateTime::createFromFormat('Y-m-d', $d1, $adminTz);
                $d1->setTime(0, 0, 0);

                if ($date < $d1) {
                    return $this->makeErrorArray(['date_invalid_range']);
                }
            }
            if ($d2) {
                $d2 = \DateTime::createFromFormat('Y-m-d', $d2, $adminTz);
                $d2->setTime(23, 59, 59);

                if ($date > $d2) {
                    return $this->makeErrorArray(['date_invalid_range']);
                }
            }

        // "Days from now"
        } elseif ($this->field_def->getOption('date_valid_type') == 'range') {
            if ($contextData && isset($contextData['exist_ticket'])) {
                /** @var \DateTime $now */
                $now = clone $contextData['exist_ticket']->date_created;
                $now->setTimezone($adminTz);
            } else {
                $now = new \DateTime('now', $adminTz);
            }

            $days1 = (int) $this->field_def->getOption('date_valid_range1');
            $days2 = (int) $this->field_def->getOption('date_valid_range2');

            $d1 = clone $now;
            $d1->modify("-{$days1} days");
            $d1->setTime(0, 0, 0);

            $d2 = clone $now;
            $d2->modify("+{$days2} days");
            $d2->setTime(23, 59, 59);

            if ($date < $d1 || $date > $d2) {
                return $this->makeErrorArray(['date_invalid_range']);
            }
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteriaForm($data = null)
    {
        $setData = null;
        if ($data and !empty($data['value'])) {
            try {
                if (is_numeric($data['value'])) {
                    $date = new \DateTime('@'.$data['value']);
                    if ($date) {
                        $date->setTimezone(App::getCurrentPerson()->getDateTimezone());
                        $setData = $date->format('Y-m-d');
                    }
                } else {
                    $date = \DateTime::createFromFormat('Y-m-d', $data['value']);
                    if ($date) {
                        $date->setTimezone(App::getCurrentPerson()->getDateTimezone());
                        $setData = $date->format('Y-m-d');
                    }
                }
            } catch (\Exception $e) {
                $setData = null;
            }
        }

        return App::getFormFactory()->createNamedBuilder(
            $this->getFormFieldName(),
            new DateTimeType(),
            $setData,
            ['required' => false]
        )->getForm();
    }
}
