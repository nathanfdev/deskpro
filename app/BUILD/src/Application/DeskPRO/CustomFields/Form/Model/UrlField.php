<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

/**
 * Class UrlField.
 */
class UrlField extends CustomFieldAbstract
{
    /**
     * @var bool
     */
    public $allowFile = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->required                 = (bool) $this->_field->getOption('required');
        $this->agent_required           = (bool) $this->_field->getOption('agent_required');
        $this->agent_validation_resolve = (bool) $this->_field->getOption('agent_validation_resolve');
        $this->allowFile                = (bool) $this->_field->getOption('allow_file');
    }

    /**
     * {@inheritdoc}
     */
    protected function setFieldProperties()
    {
        $this->_field->setOption('required', $this->required);
        $this->_field->setOption('agent_required', $this->agent_required);
        $this->_field->setOption('agent_validation_resolve', $this->agent_validation_resolve);
        $this->_field->setOption('allow_file', $this->allowFile);
    }
}
