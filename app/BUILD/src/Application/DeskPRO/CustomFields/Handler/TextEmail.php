<?php

namespace Application\DeskPRO\CustomFields\Handler;

/**
 * Handles the text field.
 */
class TextEmail extends Text
{
    /**
     * {@inheritdoc}
     */
    public function renderHtml($data = null, array $template_vars = [])
    {
        if ($data === null) {
            return '';
        }

        $html = '<a href="mailto:'.htmlspecialchars($data['value']).'">'.htmlspecialchars($data['value']).'</a>';

        return $html;
    }
}
