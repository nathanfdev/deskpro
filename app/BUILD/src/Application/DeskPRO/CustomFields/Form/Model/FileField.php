<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

/**
 * Class FileField.
 */
class FileField extends CustomFieldAbstract
{
    const EXT_LIMIT_MODE_ANY      = 'any';
    const EXT_LIMIT_MODE_ALLOW    = 'allow';
    const EXT_LIMIT_MODE_DISALLOW = 'disallow';

    /**
     * @var bool
     */
    public $multiple = false;

    /**
     * @var string
     */
    public $userExtensionsLimitMode = self::EXT_LIMIT_MODE_ANY;

    /**
     * @var string[]
     */
    public $userMustExtensions = [];

    /**
     * @var string[]
     */
    public $userNotExtensions = [];

    /**
     * @var int
     */
    public $userMaxFileSize;

    /**
     * @var string
     */
    public $agentExtensionsLimitMode = self::EXT_LIMIT_MODE_ANY;

    /**
     * @var string[]
     */
    public $agentMustExtensions = [];

    /**
     * @var string[]
     */
    public $agentNotExtensions = [];

    /**
     * @var int
     */
    public $agentMaxFileSize;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->required                 = (bool) $this->_field->getOption('required');
        $this->agent_required           = (bool) $this->_field->getOption('agent_required');
        $this->agent_validation_resolve = (bool) $this->_field->getOption('agent_validation_resolve');
        $this->multiple                 = (bool) $this->_field->getOption('multiple');
        $this->userExtensionsLimitMode  = $this->_field->getOption('user_extensions_limit_mode');
        $this->userMustExtensions       = $this->_field->getOption('user_must_extensions');
        $this->userNotExtensions        = $this->_field->getOption('user_not_extensions');
        $this->userMaxFileSize          = $this->_field->getOption('user_max_file_size');
        $this->agentExtensionsLimitMode = $this->_field->getOption('agent_extensions_limit_mode');
        $this->agentMustExtensions      = $this->_field->getOption('agent_must_extensions');
        $this->agentNotExtensions       = $this->_field->getOption('agent_not_extensions');
        $this->agentMaxFileSize         = $this->_field->getOption('agent_max_file_size');
    }

    /**
     * {@inheritdoc}
     */
    protected function setFieldProperties()
    {
        $this->_field->setOption('required', $this->required);
        $this->_field->setOption('agent_required', $this->agent_required);
        $this->_field->setOption('agent_validation_resolve', $this->agent_validation_resolve);
        $this->_field->setOption('multiple', $this->multiple);
        $this->_field->setOption('user_extensions_limit_mode', $this->userExtensionsLimitMode);
        $this->_field->setOption('user_must_extensions', $this->userMustExtensions);
        $this->_field->setOption('user_not_extensions', $this->userNotExtensions);
        $this->_field->setOption('user_max_file_size', $this->userMaxFileSize);
        $this->_field->setOption('agent_extensions_limit_mode', $this->agentExtensionsLimitMode);
        $this->_field->setOption('agent_must_extensions', $this->agentMustExtensions);
        $this->_field->setOption('agent_not_extensions', $this->agentNotExtensions);
        $this->_field->setOption('agent_max_file_size', $this->agentMaxFileSize);
    }
}
