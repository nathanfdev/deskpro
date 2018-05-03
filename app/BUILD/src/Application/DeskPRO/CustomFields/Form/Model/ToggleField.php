<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

class ToggleField extends CustomFieldAbstract
{
    /** @var string */
    public $default_value = '';
    /** @var string */
    public $label_text = '';
    /** @var string */
    public $unchecked_text = '';

    public function init()
    {
        $this->default_value  = $this->_field->default_value == '1' ? true : false;
        $this->label_text     = $this->_field->getOption('label_text') ?: '';
        $this->unchecked_text = $this->_field->getOption('unchecked_text') ?: '';
    }

    protected function setFieldProperties()
    {
        $field = $this->_field;

        $field->default_value = $this->default_value;

        if ($this->label_text) {
            $field->setOption('label_text', $this->label_text);
        } else {
            $field->setOption('label_text', null);
        }

        if ($this->unchecked_text) {
            $field->setOption('unchecked_text', $this->unchecked_text);
        } else {
            $field->setOption('unchecked_text', null);
        }
    }
}
