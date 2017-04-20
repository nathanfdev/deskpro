<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\TicketSla;

function post_ticket_batch(
    array $newTicketIds,
    \Doctrine\DBAL\Connection $db,
    \Faker\Generator $faker
) {
    $slas = [];
    $sla  = $db->fetchColumn('SELECT id FROM slas WHERE sla_type = ?', ['first_response']);
    foreach ($newTicketIds as $ticketId) {
        $status = $faker->randomElement(
            [TicketSla::STATUS_OK, TicketSla::STATUS_WARNING, TicketSla::STATUS_FAIL]
        );
        $slas[] = [
            'ticket_id'  => $ticketId,
            'sla_id'     => $sla,
            'sla_status' => $status,
            'warn_date'  => $status === TicketSla::STATUS_WARNING ?
                $faker->dateTimeBetween('-14 days', '-10 days')->format('Y-m-d H:i:s') : null,
            'fail_date' => $status === TicketSla::STATUS_FAIL ?
                $faker->dateTimeBetween('-14 days', '-10 days')->format('Y-m-d H:i:s') : null,
            'is_completed'         => 0,
            'completed_time_taken' => $faker->numberBetween(60 * 60 * 24, 60 * 60 * 24 * 10),
        ];
    }
    $db->batchInsert('ticket_slas', $slas, true);
}
