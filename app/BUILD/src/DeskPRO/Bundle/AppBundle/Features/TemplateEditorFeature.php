<?php

namespace DeskPRO\Bundle\AppBundle\Features;

/**
 * Class TemplateEditorFeature.
 */
class TemplateEditorFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'template_editor';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'New Template Editor';
    }

    public function getShortDescription()
    {
        return 'Improved portal template editor.';
    }

    public function getEnableDescription()
    {
        return <<<'HTML'
Enable new templates editor<br/><br/>
New templates editor replace the current interface in User interface.

HTML;
    }

    public function getDisableDescription()
    {
        return <<<'HTML'
Disabling New templates editor will revert your helpdesk back to using the previous editor system. 
HTML;
    }

    public function getAvailability()
    {
        return [self::AVAILABLE_AT_QA];
    }

    public function needAgentReload()
    {
        return false;
    }
}
