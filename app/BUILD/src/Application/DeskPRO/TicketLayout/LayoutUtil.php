<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use DeskPRO\Bundle\AppBundle\Form\FormFields;

class LayoutUtil
{
    /**
     * @param Layout $layout
     */
    public static function ensureMinimumUserLayout(Layout $layout)
    {
        if (!$layout->has(FormFields::MESSAGE)) {
            $layout->prepend(new LayoutField(FormFields::MESSAGE));
        }
        if (!$layout->has(FormFields::SUBJECT)) {
            $layout->prepend(new LayoutField(FormFields::SUBJECT));
        }
        if (!$layout->has(FormFields::PERSON)) {
            $layout->prepend(new LayoutField(FormFields::PERSON));
        }
        if (!$layout->has(FormFields::ATTACHMENTS)) {
            $layout->prepend(new LayoutField(FormFields::ATTACHMENTS));
        }
    }

    /**
     * @param Layout $layout
     */
    public static function ensureMinimumAgentLayout(Layout $layout)
    {
        // nothing at the moment
    }
}
