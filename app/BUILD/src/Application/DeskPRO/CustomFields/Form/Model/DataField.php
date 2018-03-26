<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

class DataField extends CustomFieldAbstract
{
    /** @var string */
    public $usersource_id;
    /** @var string */
    public $field_name;

    public function init()
    {
        $this->usersource_id = $this->_field->getOption('usersource_id');
        $this->field_name    = $this->_field->getOption('field_name');
    }

    protected function setFieldProperties()
    {
        $field = $this->_field;

        if ($this->usersource_id) {
            $field->setOption('usersource_id', $this->usersource_id);
        } else {
            $field->setOption('usersource_id', null);
        }
        if ($this->field_name) {
            $field->setOption('field_name', $this->field_name);
        } else {
            $field->setOption('field_name', null);
        }
    }
}
