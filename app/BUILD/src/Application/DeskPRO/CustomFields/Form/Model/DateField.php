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

namespace Application\DeskPRO\CustomFields\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Validator\HasValidationMetadataInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class DateField extends CustomFieldAbstract implements HasValidationMetadataInterface
{
    const DEFAULT_CALENDAR = 'gregorian';

    /** @var string */
    public $default_value = '';
    /** @var string */
    public $default_mode = 'current';
    /** @var bool */
    public $required = false;
    /** @var bool */
    public $agent_required = false;
    /** @var null */
    public $date_timezone = null;

    /** @var string|null */
    public $date_valid_type = null;
    /** @var string|null */
    public $date_valid_date1 = null;
    /** @var string|null */
    public $date_valid_date2 = null;
    /** @var int|null */
    public $date_valid_range1 = null;
    /** @var int|null */
    public $date_valid_range2 = null;
    /** @var array|null */
    public $date_valid_dow = null;
    /** @var string */
    public $calendar;

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('default_value', new Date());
    }

    public function init()
    {
        $this->default_value = $this->_field->default_value;
        $this->default_mode  = $this->_field->getOption('default_mode');

        if (empty($this->default_value)) {
            $this->default_value = date('m/d/Y');
        }

        if (empty($this->default_mode)) {
            $this->default_mode = '0';
        }

        if ($this->_field->getOption('required')) {
            $this->required = true;
        }
        if ($this->_field->getOption('agent_required')) {
            $this->agent_required = true;
        }
        if ($this->_field->getOption('agent_validation_resolve')) {
            $this->agent_validation_resolve = true;
        }

        if ($this->_field->getOption('date_valid_dow')) {
            $this->date_valid_dow = $this->_field->getOption('date_valid_dow');
        } else {
            $this->date_valid_dow = range(0, 6);
        }

        if ($this->_field->getOption('date_valid_type')) {
            $this->date_valid_type = $this->_field->getOption('date_valid_type');

            if ($this->date_valid_type == 'date' && ($this->_field->getOption('date_valid_date1') || $this->_field->getOption('date_valid_date2'))) {
                $this->date_valid_date1 = $this->_field->getOption('date_valid_date1') ?: null;
                $this->date_valid_date2 = $this->_field->getOption('date_valid_date2') ?: null;
            } elseif ($this->date_valid_type == 'range' && ($this->_field->getOption('date_valid_range1') || $this->_field->getOption('date_valid_range2'))) {
                $this->date_valid_range1 = $this->_field->getOption('date_valid_range1') ?: null;
                $this->date_valid_range2 = $this->_field->getOption('date_valid_range2') ?: null;
            } else {
                $this->date_valid_type = null;
            }
        }

        $this->calendar = $this->_field->getOption('calendar') ?: self::DEFAULT_CALENDAR;
    }

    protected function setFieldProperties()
    {
        $field                = $this->_field;
        $field->default_value = $this->default_value;
        $field->setOption('default_mode', $this->default_mode);

        $field->setOption('required', (bool) $this->required);
        $field->setOption('agent_required', (bool) $this->agent_required);

        if ($this->date_valid_dow && count($this->date_valid_dow) != 7) {
            $field->setOption('date_valid_dow', $this->date_valid_dow);
        } else {
            $field->setOption('date_valid_dow', null);
        }

        $field->setOption('date_valid_type', null);
        $field->setOption('date_valid_date1', null);
        $field->setOption('date_valid_date2', null);
        $field->setOption('date_valid_range1', null);
        $field->setOption('date_valid_range2', null);

        $this->date_valid_range1 = (int) $this->date_valid_range1;
        $this->date_valid_range2 = (int) $this->date_valid_range2;

        // Date range
        if ($this->date_valid_type == 'date' && ($this->date_valid_date1 || $this->date_valid_date2)) {
            $d1 = $d2 = null;

            // Verify dates
            if ($this->date_valid_date1) {
                try {
                    $d1 = \DateTime::createFromFormat('Y-m-d', $this->date_valid_date1);
                    if (!$d1) {
                        $this->date_valid_date1 = null;
                    }
                } catch (\Exception $e) {
                    $this->date_valid_date1 = null;
                }
            }

            if ($this->date_valid_date2) {
                try {
                    $d2 = \DateTime::createFromFormat('Y-m-d', $this->date_valid_date2);
                    if (!$d2) {
                        $this->date_valid_date2 = null;
                    }
                } catch (\Exception $e) {
                    $this->date_valid_date2 = null;
                }
            }

            if ($this->date_valid_date1 || $this->date_valid_date2) {
                if ($this->date_valid_date1 && $this->date_valid_date2) {
                    if ($d1 > $d2) {
                        $tmp                    = $this->date_valid_date1;
                        $this->date_valid_date1 = $this->date_valid_date2;
                        $this->date_valid_date2 = $tmp;
                    }
                }

                $field->setOption('date_valid_type', 'date');
                $field->setOption('date_valid_date1', $this->date_valid_date1);
                $field->setOption('date_valid_date2', $this->date_valid_date2);
            }

        // Day ranges
        } elseif ($this->date_valid_type == 'range' && ($this->date_valid_range1 || $this->date_valid_range2)) {
            if ($this->date_valid_range1 && $this->date_valid_range2) {
                if ($this->date_valid_range1 > $this->date_valid_range2) {
                    $tmp                     = $this->date_valid_range1;
                    $this->date_valid_range1 = $this->date_valid_range2;
                    $this->date_valid_range2 = $tmp;
                }
            }

            $field->setOption('date_valid_type', 'range');
            $field->setOption('date_valid_range1', (int) $this->date_valid_range1);
            $field->setOption('date_valid_range2', (int) $this->date_valid_range2);
        }

        $field->setOption('date_valid_timezone', App::getCurrentPerson()->getTimezone());
        $field->setOption('calendar', $this->calendar);
    }
}
