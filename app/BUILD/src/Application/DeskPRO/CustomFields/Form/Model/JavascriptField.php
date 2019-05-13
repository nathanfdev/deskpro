<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

/**
 * Class CurrencyField.
 */
class JavascriptField extends TextareaField
{
    public $code;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->code = $this->_field->getOption('code');
    }

    /**
     * {@inheritdoc}
     */
    protected function setFieldProperties()
    {
        parent::setFieldProperties();
        $this->_field->setOption('code', $this->code);
    }
}
