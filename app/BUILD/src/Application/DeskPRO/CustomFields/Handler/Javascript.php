<?php

namespace Application\DeskPRO\CustomFields\Handler;

/**
 * Handles the text field.
 */
class Javascript extends HandlerAbstract
{
    public function getDataFromForm(array $formData)
    {
        // TODO: Implement getDataFromForm() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCapabilities()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterCapabilities()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchType()
    {
        return 'none';
    }

    /**
     * @return string
     */
    public function getWidgetName()
    {
        return 'javascript';
    }
}
