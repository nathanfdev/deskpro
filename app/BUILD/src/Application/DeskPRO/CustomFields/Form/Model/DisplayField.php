<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

class DisplayField extends TextField
{
    /** @var string */
    public $html = '';

    public function init()
    {
        $this->html = $this->_field->getOption('html');
    }

    protected function setFieldProperties()
    {
        $field = $this->_field;

        $field->setOption('html', $this->html);
    }
}
