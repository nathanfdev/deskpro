<?php

namespace Application\DeskPRO\CustomFields\Handler;

/**
 * Handles the text field.
 */
class Textarea extends Text
{
    /**
     * {@inheritdoc}
     */
    public function getRenderTemplateVars($context = 'html')
    {
        if ($context == 'html') {
            return ['nl2br' => true];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetName()
    {
        return 'textarea';
    }
}
