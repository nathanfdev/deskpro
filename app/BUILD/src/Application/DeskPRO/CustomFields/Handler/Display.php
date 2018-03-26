<?php

namespace Application\DeskPRO\CustomFields\Handler;

use Orb\Util\Strings;

/**
 * A display field doesn't actually have any form or anything (unless of course a plugin
 * says it does).
 */
class Display extends HandlerAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getWidgetName()
    {
        return 'hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function renderHtml($data = null, array $template_vars = [])
    {
        return $this->field_def->getOption('html');
    }

    /**
     * {@inheritdoc}
     */
    public function renderText($data = null, array $template_vars = [])
    {
        return Strings::stripTags($this->field_def->getOption('html'));
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFromForm(array $formData)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCapabilities()
    {
        // Not searchable by default
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchType()
    {
        return 'display';
    }
}
