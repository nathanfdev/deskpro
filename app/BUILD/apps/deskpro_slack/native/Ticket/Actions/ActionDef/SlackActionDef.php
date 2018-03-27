<?php

/**
 * DeskPRO.
 *
 * @category Slack
 */

namespace deskpro_slack\Ticket\Actions\ActionDef;

use Application\DeskPRO\Tickets\Actions\ActionDef\AbstractActionDef;

class SlackActionDef extends AbstractActionDef
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Announce to Slack';
    }

    /**
     * {@inheritdoc}
     */
    public function getTriggerActionClass()
    {
        return 'deskpro_slack\\Ticket\\Actions\\SlackAction';
    }

    /**
     * {@inheritdoc}
     */
    public function getActionBuilderTemplate()
    {
        return 'Apps:deskpro_slack:type-actions-input.html';
    }

    /**
     * Makes sure 'channel' key is set, and adds 'app_id'.
     *
     * @param array $options
     *
     * @return array
     */
    public function processActionBuilderOptions(array $options)
    {
        if (!isset($options['channel'])) {
            $options['channel'] = '';
        }

        $options['app_id'] = $this->getActionDef()->app->id;

        return $options;
    }
}
