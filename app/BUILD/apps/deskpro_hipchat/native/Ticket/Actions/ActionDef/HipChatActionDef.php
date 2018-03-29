<?php

/**
 * DeskPRO.
 *
 * @category HipChat
 */

namespace deskpro_hipchat\Ticket\Actions\ActionDef;

use Application\DeskPRO\Tickets\Actions\ActionDef\AbstractActionDef;

class HipChatActionDef extends AbstractActionDef
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Announce to HipChat';
    }

    /**
     * {@inheritdoc}
     */
    public function getTriggerActionClass()
    {
        return 'deskpro_hipchat\\Ticket\\Actions\\HipChatAction';
    }

    /**
     * {@inheritdoc}
     */
    public function getActionBuilderTemplate()
    {
        return 'Apps:deskpro_hipchat:type-actions-input.html';
    }

    /**
     * Makes sure 'room' key is set, and adds 'app_id'.
     *
     * @param array $options
     *
     * @return array
     */
    public function processActionBuilderOptions(array $options)
    {
        if (!isset($options['room'])) {
            $options['room'] = '';
        }

        $options['app_id'] = $this->getActionDef()->app->id;

        return $options;
    }
}
