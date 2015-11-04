<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\App;
use Application\DeskPRO\Form\Type\CriteriaFilterField\DateTimeType;

/**
 * Handles the datetime field.
 */
class DateTime extends Date
{
    public function getDataFromForm(array $form_data)
    {
        $name = $this->getFormFieldName();

        $value = null;
        if (!empty($form_data[$name])) {
            $value = $form_data[$name];
        }

        if (!$value) {
            return array();
        }

        $date = \DateTime::createFromFormat($this->getFormat(), $value, App::getCurrentPerson()->getDateTimezone());
        if (!$date) {
            return array();
        }

        $date = \Orb\Util\Dates::convertToUtcDateTime($date);

        return array(
            array($this->field_def['id'], 'value', $date->getTimestamp()),
        );
    }

    protected function getFormat()
    {
        return 'Y-m-d H:i';
    }

    public function validateFormData(array $form_data, $context = self::CONTEXT_USER, $context_data = null)
    {
        $data = isset($form_data[$this->getFormFieldName()]) ? $form_data[$this->getFormFieldName()] : '';

        if ($data && !is_scalar($data)) {
            return $this->makeErrorArray(array('date_invalid'));
        }

        // Timestamp value
        if (strlen($data) == 10 && ctype_digit($data)) {
            $data = date($this->getFormat(), $data);
        }

        #------------------------------
        # Validate options
        #------------------------------

        $opt_prefix = '';
        if ($context == self::CONTEXT_AGENT) {
            $opt_prefix = 'agent_';
        }

        $options = array();
        foreach (array('required') as $k) {
            $options[$k] = $this->field_def->getOption($opt_prefix.$k);
        }

        if ($options['required']) {
            if (!$data) {
                return $this->makeErrorArray(array('required'));
            }
        }

        if ($data) {
            $date = \DateTime::createFromFormat($this->getFormat(), $data, App::getCurrentPerson()->getDateTimezone());
            if (!$date) {
                return $this->makeErrorArray(array('date_invalid'));
            }
        } else {
            return array();
        }

        #------------------------------
        # Validate ranges
        #------------------------------

        try {
            $admin_tz = new \DateTimeZone($this->field_def->getOption('date_valid_timezone'));
        } catch (\Exception $e) {
            $admin_tz = App::getCurrentPerson()->getDateTimezone();
        }

        $date_admin = clone $date;
        $date_admin->setTimezone($admin_tz);

        $dow = intval($date_admin->format('N')) - 1;

        // Days of week
        if ($valid_dow = $this->field_def->getOption('date_valid_dow')) {
            if (!in_array($dow, $valid_dow)) {
                return $this->makeErrorArray(array('date_invalid_dow'));
            }
        }

        // Specific date ranges
        if ($this->field_def->getOption('date_valid_type') == 'date') {
            $d1 = $this->field_def->getOption('date_valid_date1');
            $d2 = $this->field_def->getOption('date_valid_date2');

            if ($d1) {
                $d1 = \DateTime::createFromFormat('Y-m-d', $d1, $admin_tz);
                $d1->setTime(0, 0, 0);

                if ($date_admin < $d1) {
                    return $this->makeErrorArray(array('date_invalid_range'));
                }
            }
            if ($d2) {
                $d2 = \DateTime::createFromFormat('Y-m-d', $d2, $admin_tz);
                $d2->setTime(23, 59, 59);

                if ($date_admin > $d2) {
                    return $this->makeErrorArray(array('date_invalid_range'));
                }
            }

        // "Days from now"
        } elseif ($this->field_def->getOption('date_valid_type') == 'range') {
            if ($context_data && isset($context_data['exist_ticket'])) {
                $now = clone $context_data['exist_ticket']->date_created;
                $now->setTimezone($admin_tz);
            } else {
                $now = new \DateTime('now', $admin_tz);
            }

            $days1 = (int) $this->field_def->getOption('date_valid_range1');
            $days2 = (int) $this->field_def->getOption('date_valid_range2');

            $d1 = clone $now;
            $d1->modify("-{$days1} days");
            $d1->setTime(0, 0, 0);

            $d2 = clone $now;
            $d2->modify("+{$days2} days");
            $d2->setTime(23, 59, 59);

            if ($date_admin < $d1 || $date_admin > $d2) {
                return $this->makeErrorArray(array('date_invalid_range'));
            }
        }

        return array();
    }

    public function getSearchCriteriaForm($data = null)
    {
        $setData = null;
        if ($data and !empty($data['value'])) {
            try {
                if (ctype_digit($data['value'])) {
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
            array('required' => false)
        )->getForm();
    }
}
