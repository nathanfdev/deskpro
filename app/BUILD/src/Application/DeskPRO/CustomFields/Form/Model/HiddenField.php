<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

class HiddenField extends CustomFieldAbstract
{
    /** @var string */
    public $default_value = '';
    /** @var null */
    public $cookie_name = null;
    /** @var null */
    public $param_name = null;

    public function init()
    {
        $this->default_value = $this->_field->default_value;
        $this->cookie_name   = $this->_field->getOption('cookie_name') ?: '';
        $this->param_name    = $this->_field->getOption('param_name') ?: '';
    }

    protected function setFieldProperties()
    {
        $field = $this->_field;

        $field->default_value = $this->default_value;

        $field->setOption('cookie_name', $this->cookie_name ?: null);
        $field->setOption('param_name', $this->param_name ?: null);
    }
}
