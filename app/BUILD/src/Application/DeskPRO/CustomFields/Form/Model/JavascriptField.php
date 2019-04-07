<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

/**
 * Class CurrencyField.
 */
class JavascriptField extends CustomFieldAbstract
{
    private $jsCode;

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->required                 = (bool) $this->_field->getOption('required');
        $this->agent_required           = (bool) $this->_field->getOption('agent_required');
        $this->agent_validation_resolve = (bool) $this->_field->getOption('agent_validation_resolve');
        $this->jsCode                   = (bool) $this->_field->getOption('js_code');
    }

    /**
     * {@inheritdoc}
     */
    protected function setFieldProperties()
    {
        $this->_field->setOption('required', $this->required);
        $this->_field->setOption('agent_required', $this->agent_required);
        $this->_field->setOption('agent_validation_resolve', $this->agent_validation_resolve);
        $this->_field->setOption('js_code', $this->jsCode);
    }
}
