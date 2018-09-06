<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity;
use Orb\Util\Arrays;

class TicketFlagged extends AbstractEntityRepository
{
    public function getFlagForTicket($ticket, Entity\Person $person)
    {
        $color = App::getDb()->fetchColumn('
            SELECT color
            FROM tickets_flagged
            WHERE ticket_id = ? AND person_id = ?
        ', [$ticket->id, $person->id]);

        return $color;
    }

    public function getFlagsForTickets($tickets, Entity\Person $person)
    {
        $ids = Arrays::flattenToIndex($tickets, 'id');

        if (!$ids) {
            return [];
        }

        return $this->getEntityManager()->getConnection()->fetchAllKeyValue('
            SELECT ticket_id, color
            FROM tickets_flagged
            WHERE ticket_id IN(?) AND person_id = ?
        ', [$ids, $person['id']], [Connection::PARAM_INT_ARRAY, \PDO::PARAM_INT]);
    }

    public function getCountsForPerson(Entity\Person $person)
    {
        $person->loadHelper('Agent');
        $person->loadHelper('AgentTeam');
        $person->loadHelper('AgentPermissions');
        $person->loadHelper('PermissionsManager');

        $assigned_perm_part = "tickets.agent_id = {$person['id']}";
        if ($person->getTeamIds()) {
            $assigned_perm_part = "($assigned_perm_part OR tickets.agent_team_id IN (".implode(',', $person->getTeamIds()).'))';
        }

        $where_perm = [];

        if ($person->getDisallowedDepartments()) {
            $where_perm[] = '(tickets.department_id NOT IN ('.implode(',', $person->getDisallowedDepartments()).") OR tickets.department_id IS NULL OR $assigned_perm_part)";
        }

        if (!$person->hasPerm('agent_tickets.view_unassigned')) {
            $where_perm[] = 'tickets.agent_id IS NOT NULL';
        }

        if (!$person->hasPerm('agent_tickets.view_others')) {
            $part   = [];
            $part[] = "tickets.agent_id = {$person['id']}";
            if ($person->getAgentTeamIds()) {
                $part[] = 'tickets.agent_team_id IN ('.implode(',', $person->getAgentTeamIds()).')';
            }
            if ($person->hasPerm('agent_tickets.view_unassigned')) {
                $part[] = 'tickets.agent_id IS NULL';
            }

            $where_perm[] = '('.implode(' OR ', $part).')';
        }

        if ($where_perm) {
            $where_perm = '('.implode(' AND ', $where_perm).')';
        } else {
            $where_perm = '1';
        }

        return App::getDb()->fetchAllKeyValue("
            SELECT tickets_flagged.color, COUNT(*)
            FROM tickets_flagged
            LEFT JOIN tickets ON (tickets.id = tickets_flagged.ticket_id)
            WHERE tickets_flagged.person_id = ? AND tickets.status != 'hidden' AND $where_perm
            GROUP BY tickets_flagged.color
        ", [$person['id']]);
    }
}
