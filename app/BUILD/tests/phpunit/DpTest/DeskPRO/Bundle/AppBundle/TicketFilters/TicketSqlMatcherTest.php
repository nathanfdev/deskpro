<?php

namespace DpTest\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSearchParams;

class TicketSqlMatcherTest extends BaseTicketSqlMatcherTest
{
    public function test_id_match()
    {
        $this->assertEqualQuery(
            'ticket.id = 1',
            'SELECT COUNT(*) AS count FROM tickets_search_active tickets WHERE tickets.id = :c0',
            ['c0' => 1]
        );
    }

    public function test_builtin_assigned_to_me()
    {
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\' AND ticket.agent = $me',
            'SELECT COUNT(*) AS count FROM tickets_search_active tickets WHERE (tickets.status = :c0) AND (tickets.agent_id = :c1)',
            ['c0' => 'awaiting_agent', 'c1' => 1]
        );
    }

    public function test_builtin_follow()
    {
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\' AND ticket.followers HAS $me',
            'SELECT
               COUNT(*) AS count FROM tickets_search_active tickets
               LEFT JOIN tickets_participants c1_part ON c1_part.ticket_id = tickets.id
               WHERE (tickets.status = :c0) AND (c1_part.person_id IN (:c2))',
            ['c0' => 'awaiting_agent', 'c2' => [1]]
        );
    }

    public function test_builtin_assigned_to_team()
    {
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\' AND ticket.agent_team IN $my_teams',
            'SELECT COUNT(*) AS count FROM tickets_search_active tickets WHERE (tickets.status = :c0) AND (tickets.agent_team_id IN (:c1))',
            ['c0' => 'awaiting_agent', 'c1' => [1, 2, 3]]
        );
    }

    public function test_builtin_unassigned()
    {
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\' AND ticket.agent IS EMPTY',
            'SELECT COUNT(*) AS count FROM tickets_search_active tickets WHERE (tickets.status = :c0) AND (tickets.agent_id IS NULL)',
            ['c0' => 'awaiting_agent']
        );
    }

    public function test_builtin_awaiting_agent()
    {
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\'',
            'SELECT COUNT(*) AS count FROM tickets_search_active tickets WHERE tickets.status = :c0',
            ['c0' => 'awaiting_agent']
        );
    }

    public function test_group_by()
    {
        $params = new TicketSearchParams();
        $params->groupBy(TicketSearchParams::GROUP_AGENT);
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\'',
            'SELECT COUNT(*) AS count, tickets.agent_id AS group_field0 FROM tickets_search_active tickets WHERE tickets.status = :c0 GROUP BY tickets.agent_id WITH ROLLUP',
            ['c0' => 'awaiting_agent'],
            $params
        );
    }

    public function test_group_by_two()
    {
        $params = new TicketSearchParams();
        $params->groupBy(TicketSearchParams::GROUP_SLA_SEVERITY);
        $params->groupBy(TicketSearchParams::GROUP_AGENT);
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\'',
            'SELECT COUNT(*) AS count,
                MAX (FIELD(grouping0.sla_status, \'ok\', \'warning\', \'fail\' ) ) AS group_field0,
                tickets.agent_id AS group_field1
            FROM tickets_search_active tickets
            LEFT JOIN ticket_slas grouping0 ON grouping0.ticket_id = tickets.id
            WHERE tickets.status = :c0
            GROUP BY group_field0, tickets.agent_id WITH ROLLUP',
            ['c0' => 'awaiting_agent'],
            $params
        );
    }

    public function test_order_by()
    {
        $params = new TicketSearchParams();
        $params->orderBy(TicketSearchParams::ORDER_DATE_CREATED);
        $this->assertEqualIdQuery(
            'ticket.status = \'awaiting_agent\'',
            'SELECT tickets.id FROM tickets_search_active tickets WHERE tickets.status = :c0 ORDER BY tickets.date_created ASC',
            ['c0' => 'awaiting_agent'],
            $params
        );
    }

    public function test_order_by_two()
    {
        $params = new TicketSearchParams();
        $params->orderBy(TicketSearchParams::ORDER_URGENCY, 'DESC');
        $params->orderBy(TicketSearchParams::ORDER_DATE_CREATED, 'ASC');
        $this->assertEqualIdQuery(
            'ticket.status = \'awaiting_agent\'',
            'SELECT tickets.id FROM tickets_search_active tickets WHERE tickets.status = :c0 ORDER BY tickets.urgency DESC, tickets.date_created ASC',
            ['c0' => 'awaiting_agent'],
            $params
        );
    }

    public function test_order_last_reply()
    {
        $params = new TicketSearchParams();
        $params->orderBy(TicketSearchParams::ORDER_DATE_LAST_REPLY);
        $this->assertEqualIdQuery(
            'ticket.status = \'awaiting_agent\'',
            'SELECT tickets.id FROM tickets_search_active tickets WHERE tickets.status = :c0
            ORDER BY GREATEST(
                COALESCE(tickets.date_last_agent_reply, \'0000-00-00\'),
                COALESCE(tickets.date_last_user_reply, \'0000-00-00\'),
                tickets.date_created
            ) ASC
            ',
            ['c0' => 'awaiting_agent'],
            $params
        );
    }

    public function test_subfilter_by()
    {
        $params = new TicketSearchParams();
        $params->subFilterBy(TicketSearchParams::GROUP_AGENT, 5);
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\'',
            'SELECT COUNT(*) AS count FROM tickets_search_active tickets
            WHERE (tickets.status = :c0) AND (tickets.agent_id = :subfilterval0)',
            ['c0' => 'awaiting_agent', 'subfilterval0' => 5],
            $params
        );
    }

    public function test_subfilter_by_two()
    {
        $params = new TicketSearchParams();
        $params->subFilterBy(TicketSearchParams::GROUP_AGENT, 5);
        $params->subFilterBy(TicketSearchParams::GROUP_DEPARTMENT, 6);
        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\'',
            'SELECT COUNT(*) AS count FROM tickets_search_active tickets
            WHERE (tickets.status = :c0) AND (tickets.agent_id = :subfilterval0) AND (tickets.department_id = :subfilterval1)',
            ['c0' => 'awaiting_agent', 'subfilterval0' => 5, 'subfilterval1' => 6],
            $params
        );
    }

    public function test_custom_choice()
    {
        $this->assertEqualQuery(
            'ticket.data.1 = 10',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN custom_data_ticket c0_dat ON c0_dat.ticket_id = tickets.id AND c0_dat.root_field_id = :c1
            WHERE c0_dat.field_id = :c2
            ',
            ['c1' => 1, 'c2' => 10]
        );

        $this->assertEqualQuery(
            'ticket.data.my_choice = 10',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN custom_data_ticket c0_dat ON c0_dat.ticket_id = tickets.id AND c0_dat.root_field_id = :c1
            WHERE c0_dat.field_id = :c2
            ',
            ['c1' => 1, 'c2' => 10]
        );

        $this->assertEqualQuery(
            'ticket.data.my_choice HAS 10',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN custom_data_ticket c0_dat ON c0_dat.ticket_id = tickets.id AND c0_dat.root_field_id = :c1
            WHERE c0_dat.field_id IN (:c2)
            ',
            ['c1' => 1, 'c2' => [10]]
        );
    }

    public function test_custom_date()
    {
        $this->assertEqualQuery(
            'ticket.data.4 < "2018-04-09"',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN custom_data_ticket c0_dat ON c0_dat.ticket_id = tickets.id AND c0_dat.root_field_id = :c1
            WHERE c0_dat.value < :c2
            ',
            ['c1' => 4, 'c2' => strtotime('2018-04-09')]
        );

        $this->assertEqualQuery(
            'ticket.data.my_date > "2018-04-09"',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN custom_data_ticket c0_dat ON c0_dat.ticket_id = tickets.id AND c0_dat.root_field_id = :c1
            WHERE c0_dat.value > :c2
            ',
            ['c1' => 4, 'c2' => strtotime('2018-04-09')]
        );
    }

    public function test_custom_toggle()
    {
        $this->assertEqualQuery(
            'ticket.data.my_toggle = 1',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN custom_data_ticket c0_dat ON c0_dat.ticket_id = tickets.id AND c0_dat.root_field_id = :c1
            WHERE c0_dat.value = :c2
            ',
            ['c1' => 5, 'c2' => '1']
        );

        $this->assertEqualQuery(
            'ticket.status = \'awaiting_agent\' AND ticket.agent = $me AND ticket.data.my_toggle = 0',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN custom_data_ticket c2_dat ON c2_dat.ticket_id = tickets.id AND c2_dat.root_field_id = :c3
            WHERE (tickets.status = :c0) AND (tickets.agent_id = :c1) AND (c2_dat.value = 0 OR c2_dat.value IS NULL)
            ',
            ['c0' => 'awaiting_agent', 'c1' => 1, 'c3' => 5]
        );
    }
}
