<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;

/**
 * Goes through queued messages.
 */
class EnsureSearchTables extends AbstractJob
{
    const DEFAULT_INTERVAL = 60;

    public function run()
    {
        $last_search_refill = App::getSetting('core.last_searchtables_refill');
        if (!$last_search_refill) {
            $last_search_refill = 0;
        }

        // If we've done a refill within the last 15 mins,
        // dont try again (dont want to continously refill, could slow everything down)
        if ($last_search_refill > strtotime('-15 minutes')) {
            return;
        }

        $do_refill = false;
        if (App::getSetting('core.do_searchtables_refill')) {
            $do_refill = true;
        } else {
            // Check to see if the search table isnt already filled
            $has_one = App::getDb()->fetchColumn('SELECT id FROM tickets_search_active LIMIT 1');
            if (!$has_one) {
                $has_one = App::getDb()->fetchColumn("SELECT id FROM tickets WHERE status IN ('awaiting_agent', 'awaiting_user') LIMIT 1");
                if ($has_one) {
                    $do_refill = true;
                }
            }
        }

        if ($do_refill) {
            $settings = App::getContainer()->getSettingsHandler();
            $settings->setSetting('core.last_searchtables_refill', time());
            $settings->setSetting('core.do_searchtables_refill', 0);

            App::getEntityRepository('DeskPRO:Ticket')->fillSearchTable();
            $this->logStatus('Filled tickets_search_active table');

            App::get('event_dispatcher')->dispatch(
                LegacySystemEvent::EVENT_NAME,
                new LegacySystemEvent('agent.ui.reload', [
                    'type'        => 'admin',
                    'person_id'   => 0,
                    'person_name' => 'System',
                ]));
        }
    }
}
