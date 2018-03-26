<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

use Orb\Util\Strings;

/**
 * Class TextField.
 */
class TextField extends CustomFieldAbstract
{
    /**
     * @var int
     */
    public $min_length;

    /**
     * @var int
     */
    public $max_length;

    /**
     * @var string
     */
    public $regex;

    /**
     * @var bool
     */
    public $regex_required = false;

    /**
     * @var string
     */
    public $default_value = '';

    /**
     * @var int
     */
    public $agent_min_length;

    /**
     * @var int
     */
    public $agent_max_length;

    /**
     * @var string
     */
    public $agent_regex;

    /**
     * @var bool
     */
    public $agent_regex_required = false;

    /**
     * @var bool
     */
    public $clickable_links = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->default_value = $this->_field->getDefaultValue();

        if ($this->_field->getOption('clickable_links')) {
            $this->clickable_links = true;
        }

        if ($this->_field->getOption('min_length')) {
            $this->validation_type = 'required';
            $this->min_length      = $this->_field->getOption('min_length');
        }
        if ($this->_field->getOption('max_length')) {
            $this->validation_type = 'required';
            $this->max_length      = $this->_field->getOption('max_length');
        }
        if ($this->_field->getOption('regex')) {
            $this->validation_type = 'regex';
            $this->regex           = $this->_field->getOption('regex');
            $this->regex_required  = $this->_field->getOption('regex_required');
        }

        if ($this->_field->getOption('agent_min_length')) {
            $this->agent_validation_type = 'required';
            $this->agent_min_length      = $this->_field->getOption('agent_min_length');
        }
        if ($this->_field->getOption('agent_max_length')) {
            $this->agent_validation_type = 'required';
            $this->agent_max_length      = $this->_field->getOption('agent_max_length');
        }
        if ($this->_field->getOption('agent_regex')) {
            $this->agent_validation_type = 'regex';
            $this->agent_regex           = $this->_field->getOption('agent_regex');
            $this->agent_regex_required  = $this->_field->getOption('agent_regex_required');
        }
        if ($this->_field->getOption('agent_validation_resolve')) {
            $this->agent_validation_resolve = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setFieldProperties()
    {
        $field = $this->_field;

        $field->setDefaultValue($this->default_value);
        $field->setOption('clickable_links', $this->clickable_links);

        if ($this->min_length || $this->max_length) {
            $this->validation_type = 'required';
            $field->setOption('required', true);
            $field->setOption('min_length', $this->min_length);
            $field->setOption('max_length', $this->max_length);
            $field->setOption('regex', null);
            $field->setOption('regex_required', null);
        } elseif ($this->regex) {
            $this->regex = Strings::getInputRegexPattern($this->regex);

            $this->validation_type = 'regex';
            $field->setOption('required', null);
            $field->setOption('regex', $this->regex);
            $field->setOption('regex_required', $this->regex_required);
            $field->setOption('min_length', null);
            $field->setOption('max_length', null);
        } else {
            $this->validation_type = null;
            $field->setOption('required', null);
            $field->setOption('regex', null);
            $field->setOption('regex_required', null);
            $field->setOption('min_length', null);
            $field->setOption('max_length', null);
        }

        if ($this->agent_min_length || $this->agent_max_length) {
            $this->agent_validation_type = 'required';
            $field->setOption('agent_required', true);
            $field->setOption('agent_min_length', $this->agent_min_length);
            $field->setOption('agent_max_length', $this->agent_max_length);
            $field->setOption('agent_regex', null);
            $field->setOption('agent_regex_required', null);
        } elseif ($this->agent_regex) {
            $this->agent_validation_type = 'regex';

            $this->regex = Strings::getInputRegexPattern($this->regex);

            $field->setOption('agent_required', null);
            $field->setOption('agent_regex', $this->agent_regex);
            $field->setOption('agent_regex_required', $this->agent_regex_required);
            $field->setOption('agent_min_length', null);
            $field->setOption('agent_max_length', null);
        } else {
            $this->agent_validation_type = null;
            $field->setOption('agent_required', null);
            $field->setOption('agent_regex', null);
            $field->setOption('agent_regex_required', null);
            $field->setOption('agent_min_length', null);
            $field->setOption('agent_max_length', null);
        }
    }
}
