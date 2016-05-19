<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
