<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class TicketTrigger extends AbstractEntityRepository
{
    /**
     * @param string $event_type
     *
     * @return \Application\DeskPRO\Entity\TicketTrigger[]
     */
    public function getTriggersForEventType($event_type)
    {
        $triggers = $this->_em->createQuery('
            SELECT t
            FROM DeskPRO:TicketTrigger t
            WHERE t.event_trigger = :event_type AND t.is_enabled = true
            ORDER BY t.run_order
        ')->execute(['event_type' => $event_type]);

        return $triggers;
    }

    /**
     * @param string|null $type
     *
     * @return \Application\DeskPRO\Entity\TicketTrigger[]
     */
    public function getTriggers($type = null)
    {
        if ($type) {
            return $this->getEntityManager()->createQuery('
                SELECT t
                FROM DeskPRO:TicketTrigger t
                WHERE t.event_trigger = ?0
                ORDER BY t.run_order, t.title ASC
            ')->execute([$type]);
        } else {
            return $this->getEntityManager()->createQuery('
                SELECT t
                FROM DeskPRO:TicketTrigger t
                ORDER BY t.run_order, t.title ASC
            ')->execute();
        }
    }

    /**
     * @param array $run_orders
     */
    public function updateRunOrders(array $run_orders)
    {
        $run_orders = array_values($run_orders);

        $db = $this->_em->getConnection();
        $db->beginTransaction();

        $x = 0;
        foreach ($run_orders as $tr_id) {
            $x += 10;
            if ($tr_id == 'departments') {
                $db->executeUpdate("
                    UPDATE ticket_triggers
                    SET run_order = ?
                    WHERE department_id IS NOT NULL AND event_trigger = 'newticket'
                ", [$x]);
            } elseif ($tr_id == 'departments_changed') {
                $db->executeUpdate("
                    UPDATE ticket_triggers
                    SET run_order = ?
                    WHERE department_id IS NOT NULL AND event_trigger = 'update'
                ", [$x]);
            } elseif ($tr_id == 'emailaccounts') {
                $db->executeUpdate("
                    UPDATE ticket_triggers
                    SET run_order = ?
                    WHERE email_account_id IS NOT NULL AND event_trigger = 'newticket'
                ", [$x]);
            } else {
                $tr_id = (int) $tr_id;
                $db->update('ticket_triggers', ['run_order' => $x], ['id' => $tr_id]);
            }
        }

        $db->commit();
    }

    /**
     * @param string $labelType
     * @param string $label
     *
     * @return \Application\DeskPRO\Entity\TicketTrigger[]
     */
    public function getTriggersByLabel($labelType, $label)
    {
        return $this->_em
            ->createQuery("
                SELECT tt
                FROM DeskPRO:TicketTrigger tt
                WHERE (
                        JSON_CONTAINS(JSON_EXTRACT(tt.terms, '$.\"@DATA\".terms[*].set_terms[*].type'), :term_type) = 1
                        AND JSON_CONTAINS(JSON_EXTRACT(tt.terms, '$.\"@DATA\".terms[*].set_terms[*].options.labels'), :label) = 1
                    )
                    OR (
                        JSON_CONTAINS(JSON_EXTRACT(tt.actions, '$.\"@DATA\".actions[*].type'), :action_type) = 1
                        AND (
                            JSON_CONTAINS(JSON_EXTRACT(tt.actions, '$.\"@DATA\".actions[*].options.add_labels'), :label) = 1
                            OR JSON_CONTAINS(JSON_EXTRACT(tt.actions, '$.\"@DATA\".actions[*].options.remove_labels'), :label) = 1
                        )
                    )
            ")
            ->setParameters([
                'term_type'   => "\"{$labelType}\"",
                'action_type' => '"SetLabels"',
                'label'       => "\"{$label}\"",
            ])
            ->execute();
    }
}
