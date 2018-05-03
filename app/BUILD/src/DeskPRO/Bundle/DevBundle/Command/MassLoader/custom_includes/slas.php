<?php

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
