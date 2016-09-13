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

        if ($disallowed = $person_context->getHelperManager()->callName('getdisalloweddepartments', [])) {
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
