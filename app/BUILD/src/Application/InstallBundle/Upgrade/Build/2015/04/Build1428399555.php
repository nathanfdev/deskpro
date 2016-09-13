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
 */

namespace Application\InstallBundle\Upgrade\Build;

use Doctrine\DBAL\Connection;

class Build1428399555 extends AbstractBuild
{
    public function run()
    {
        $this->out('Correct broken HTML in some signatures');

        $cleaner = $this->container->getInputCleaner();
        $db      = $this->container->getDb();

        // Find all messages after build400
        // with a certain p that we can look for as an indicator
        // of a broken signature block
        $message_ids = $db->fetchAllCol("
            SELECT id
            FROM tickets_messages
            WHERE
                date_created >= '2015-03-17 18:27:00'
                AND creation_system = 'web.agent.portal'
                AND message LIKE '%<p class=\"dp-signature-start\">%'
        ");
        if (!$message_ids) {
            return;
        }

        $message_ids = array_chunk($message_ids, 100, false);
        foreach ($message_ids as $batch_ids) {
            $texts = $db->fetchAllKeyed('
                SELECT id, ticket_id, message
                FROM tickets_messages
                WHERE id IN (?)
            ', [$batch_ids], 'id', [Connection::PARAM_INT_ARRAY]);

            foreach ($texts as $mid => $info) {
                $msg       = $info['message'];
                $ticket_id = $info['ticket_id'];

                $msg_fixed = $cleaner->clean("<div>$msg</div>", 'html_fix');
                if ($msg_fixed && $msg_fixed !== $msg) {
                    $msg_fixed = str_replace('class="dp-signature-start"', 'style="margin:0;padding:0;"', $msg_fixed);

                    $this->out("Fixing message $mid");
                    $db->update('tickets_messages', ['message' => $msg_fixed], ['id' => $mid]);
                    $db->insert('tickets_logs', [
                        'ticket_id'   => $ticket_id,
                        'action_type' => 'message_edit',
                        'id_object'   => $mid,
                        'details'     => serialize([
                            'message_id'       => $mid,
                            'old_message'      => $msg,
                            'old_full_message' => '',
                        ]),
                    ]);
                }
            }
        }
    }
}
