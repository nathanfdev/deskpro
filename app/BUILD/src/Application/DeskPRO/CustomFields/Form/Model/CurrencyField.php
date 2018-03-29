<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

/**
 * Class CurrencyField.
 */
class CurrencyField extends CustomFieldAbstract
{
    /**
     * @var int
     */
    public $currencyId = false;

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->required                 = (bool) $this->_field->getOption('required');
        $this->agent_required           = (bool) $this->_field->getOption('agent_required');
        $this->agent_validation_resolve = (bool) $this->_field->getOption('agent_validation_resolve');
        $this->currencyId               = (bool) $this->_field->getOption('currency_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function setFieldProperties()
    {
        $this->_field->setOption('required', $this->required);
        $this->_field->setOption('agent_required', $this->agent_required);
        $this->_field->setOption('agent_validation_resolve', $this->agent_validation_resolve);
        $this->_field->setOption('currency_id', $this->currencyId);
    }
}
