<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

/**
 * Class ExternalUniqueKeyField.
 */
class ExternalUniqueKeyField extends CustomFieldAbstract
{
    /**
     * @var int
     */
    public $min_length;

    /**
     * @var int
     */
    public $agent_min_length;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->validation_type = 'required';
        if ($this->_field->getOption('min_length')) {
            $this->min_length = $this->_field->getOption('min_length');
        } else {
            $this->min_length = 1;
        }

        if ($this->_field->getOption('agent_min_length')) {
            $this->agent_min_length = $this->_field->getOption('agent_min_length');
        } else {
            $this->agent_min_length = 1;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setFieldProperties()
    {
        $field = $this->_field;

        $this->validation_type = 'required';
        $field->setOption('required', true);
        $field->setOption('min_length', $this->min_length);

        $this->agent_validation_type = 'required';
        $field->setOption('agent_required', true);
        $field->setOption('agent_min_length', $this->agent_min_length);
    }
}
