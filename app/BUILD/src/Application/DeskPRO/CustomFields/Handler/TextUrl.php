<?php

namespace Application\DeskPRO\CustomFields\Handler;

/**
 * Handles the text field.
 */
class TextUrl extends Text
{
    /**
     * {@inheritdoc}
     */
    public function renderHtml($data = null, array $template_vars = [])
    {
        if ($data === null) {
            return '';
        }

        $snipped = preg_replace('#^https?://#', '', $data['value']);
        $html    = '<a href="'.htmlspecialchars($data['value']).'" target="_blank">'.htmlspecialchars($snipped).'</a>';

        return $html;
    }
}
