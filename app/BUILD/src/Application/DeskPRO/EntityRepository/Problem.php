<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class Problem extends AbstractEntityRepository
{
    public function getCounts()
    {
        return $this->getEntityManager()->getConnection()->fetchAllKeyValue('
            SELECT p.id, COUNT(p.id)
            FROM problems p JOIN problem2tickets pt ON pt.problem_id = p.id
            GROUP BY p.id
        ');
    }

    public function getCountsForAgentInterface(array $problems, Entity\Person $person_context = null)
    {
        if (!$problems) {
            return [];
        }

        if (!$person_context) {
            $person_context = App::getCurrentPerson();
        }

        if (!$person_context->is_agent) {
            throw new \InvalidArgumentException('Person must be an agent');
        }

        $where_perm = [];

        if ($disallowed = $person_context->getHelperManager()->callName('getdisalloweddepartments', ['tickets', true])) {
            $where_perm[] = 't.department_id NOT IN ('.implode(',', $disallowed).')';
        }

        if (!$person_context->hasPerm('agent_tickets.view_unassigned')) {
            $where_perm[] = 't.agent_id IS NOT NULL';
        }

        if (!$person_context->hasPerm('agent_tickets.view_others')) {
            $part   = [];
            $part[] = "t.agent_id = {$person_context['id']}";

            if ($teams = $person_context->getHelperManager()->callName('getagentteamids', [])) {
                $part[] = 't.agent_team_id IN ('.implode(',', $teams).')';
            }

            $where_perm[] = '('.implode(' OR ', $part).')';
        }

        if (!$where_perm) {
            $where_perm[] = '1';
        }

        $where = '(('.implode(' AND ', $where_perm).') OR (';

        $where .= "t.agent_id = {$person_context['id']} OR ";
        if ($teams = $person_context->getHelperManager()->callName('getagentteamids', [])) {
            $where .= 't.agent_team_id IN ('.implode(',', $teams).') OR ';
        }

        $where .= 'tp.person_id IS NOT NULL))';

        $ids = [];
        foreach ($problems as $problem) {
            $ids[] = $problem->id;
        }

        $where .= ' AND p.id IN ('.implode(',', $ids).')';
        $where .= sprintf(' AND (t.hidden_status is null or t.hidden_status NOT IN ("%s", "%s"))', Entity\Ticket::HIDDEN_STATUS_DELETED, Entity\Ticket::HIDDEN_STATUS_SPAM);

        $results = $this->getEntityManager()->getConnection()->fetchAll(
            "
            SELECT p.id, COUNT(*) AS count
            FROM problems p
            JOIN problem2tickets pt ON p.id = pt.problem_id
            JOIN tickets t ON t.id = pt.ticket_id
            LEFT JOIN tickets_participants tp ON tp.ticket_id = t.id AND tp.person_id = {$person_context->id}
            WHERE $where
            GROUP BY  p.id
        "
        );

        $output = [];
        foreach ($results as $result) {
            $output[$result['id']] = $result['count'];
        }

        return $output;
    }
}
