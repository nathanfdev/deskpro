<?php

namespace DpTest\DeskPRO\Bundle\ReportBundle\Dpql2;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;

/**
 * Class CompilerStatementTest.
 */
class CompilerStatementTest extends AbstractCompilerTest
{
    public function test_id()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.id
FROM tickets
WHERE tickets.id IN (1, 2, 3)
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id`
FROM `tickets`
WHERE `tickets`.`id` IN (1, 2, 3)
LIMIT 2500
SQL
        );
    }

    public function test_alias()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.subject AS my_field
FROM tickets
WHERE tickets.urgency IN (1, 2, 3)
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`subject`
FROM `tickets`
WHERE `tickets`.`urgency` IN (1, 2, 3)
LIMIT 2500
SQL
        );
    }

    public function test_alias_ref()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_COUNT() AS 'Tickets'
FROM tickets
GROUP BY tickets.agent
ORDER BY @'Tickets' DESC
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ COUNT(*), `tickets_agent`.`id`,
(CASE
    WHEN (LENGTH(tickets_agent.first_name) > 0 AND LENGTH(tickets_agent.last_name) > 0) THEN CONCAT(tickets_agent.first_name, ' ', tickets_agent.last_name)
    WHEN LENGTH(tickets_agent.name) > 0 THEN tickets_agent.name
    WHEN LENGTH(tickets_agent.last_name) > 0 THEN tickets_agent.last_name
    WHEN LENGTH(tickets_agent.first_name) > 0 THEN tickets_agent.first_name
    ELSE CONCAT('ID-', tickets_agent.id)
END)
FROM `tickets`
LEFT JOIN `people` AS `tickets_agent` ON (`tickets`.`agent_id` = `tickets_agent`.`id`)
GROUP BY `tickets_agent`.`id`
ORDER BY COUNT(*) DESC
LIMIT 2500
SQL
        );
    }

    public function test_binary_comparison()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.urgency
FROM tickets
WHERE tickets.urgency > 1
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`urgency`
FROM `tickets`
WHERE (`tickets`.`urgency` > 1)
LIMIT 2500
SQL
        );
    }

    public function test_binary_interval()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.date_created + INTERVAL 1 DAY
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ (`tickets`.`date_created` + INTERVAL 1 DAY)
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_binary_math()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.urgency + 100
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ (`tickets`.`urgency` + 100)
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_column()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.subject, tickets.person
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`subject`, `tickets_person`.`id`,
(CASE
    WHEN (LENGTH(tickets_person.first_name) > 0 AND LENGTH(tickets_person.last_name) > 0) THEN CONCAT(tickets_person.first_name, ' ', tickets_person.last_name)
    WHEN LENGTH(tickets_person.name) > 0 THEN tickets_person.name
    WHEN LENGTH(tickets_person.last_name) > 0 THEN tickets_person.last_name
    WHEN LENGTH(tickets_person.first_name) > 0 THEN tickets_person.first_name
    ELSE CONCAT('ID-', tickets_person.id)
END)

FROM `tickets`
LEFT JOIN `people` AS `tickets_person` ON (`tickets`.`person_id` = `tickets_person`.`id`)
LIMIT 2500
SQL
        );
    }

    public function test_column_star()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.*
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id`, `tickets`.`ref`, `tickets`.`auth`, `tickets`.`sent_to_address`, `tickets`.`email_account_address`, `tickets`.`creation_system`, `tickets`.`creation_system_option`, `tickets`.`ticket_hash`, `tickets`.`status`, `tickets`.`hidden_status`, `tickets`.`is_hold`, `tickets`.`urgency`, `tickets`.`count_agent_replies`, `tickets`.`count_user_replies`, `tickets`.`feedback_rating`, `tickets`.`date_feedback_rating`, `tickets`.`date_created`, `tickets`.`date_resolved`, `tickets`.`date_archived`, `tickets`.`date_first_agent_assign`, `tickets`.`date_first_agent_reply`, `tickets`.`date_last_agent_reply`, `tickets`.`date_last_user_reply`, `tickets`.`date_agent_waiting`, `tickets`.`date_user_waiting`, `tickets`.`date_status`, `tickets`.`date_on_hold`, (`tickets`.`total_user_waiting` + IF(`tickets`.date_user_waiting AND `tickets`.status = 'awaiting_agent', UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`tickets`.date_user_waiting), 0)), `tickets`.`total_to_first_reply`, `tickets`.`date_locked`, `tickets`.`has_attachments`, `tickets`.`subject`, `tickets`.`original_subject`, `tickets`.`properties`, `tickets`.`worst_sla_status`, `tickets`.`waiting_times`, `tickets_parent_ticket`.`id`, `tickets_parent_ticket`.`subject`, `tickets_language`.`title`, `tickets_brand`.`name`, `tickets_department`.`title`, `tickets_category`.`title`, `tickets_priority`.`title`, `tickets_workflow`.`title`, `tickets_product`.`title`, `tickets_person`.`id`,
(CASE
    WHEN (LENGTH(tickets_person.first_name) > 0 AND LENGTH(tickets_person.last_name) > 0) THEN CONCAT(tickets_person.first_name, ' ', tickets_person.last_name)
    WHEN LENGTH(tickets_person.name) > 0 THEN tickets_person.name
    WHEN LENGTH(tickets_person.last_name) > 0 THEN tickets_person.last_name
    WHEN LENGTH(tickets_person.first_name) > 0 THEN tickets_person.first_name
    ELSE CONCAT('ID-', tickets_person.id)
END)
, `tickets_agent`.`id`,
(CASE
    WHEN (LENGTH(tickets_agent.first_name) > 0 AND LENGTH(tickets_agent.last_name) > 0) THEN CONCAT(tickets_agent.first_name, ' ', tickets_agent.last_name)
    WHEN LENGTH(tickets_agent.name) > 0 THEN tickets_agent.name
    WHEN LENGTH(tickets_agent.last_name) > 0 THEN tickets_agent.last_name
    WHEN LENGTH(tickets_agent.first_name) > 0 THEN tickets_agent.first_name
    ELSE CONCAT('ID-', tickets_agent.id)
END)
, `tickets_agent_team`.`name`, `tickets_organization`.`id`, `tickets_organization`.`name`, `tickets`.`locked_by_agent`
FROM `tickets`
LEFT JOIN `tickets` AS `tickets_parent_ticket` ON (`tickets`.`parent_ticket_id` = `tickets_parent_ticket`.`id`)
LEFT JOIN `languages` AS `tickets_language` ON (`tickets`.`language_id` = `tickets_language`.`id`)
LEFT JOIN `brands` AS `tickets_brand` ON (`tickets`.`brand_id` = `tickets_brand`.`id`)
LEFT JOIN `departments` AS `tickets_department` ON (`tickets`.`department_id` = `tickets_department`.`id`)
LEFT JOIN `ticket_categories` AS `tickets_category` ON (`tickets`.`category_id` = `tickets_category`.`id`)
LEFT JOIN `ticket_priorities` AS `tickets_priority` ON (`tickets`.`priority_id` = `tickets_priority`.`id`)
LEFT JOIN `ticket_workflows` AS `tickets_workflow` ON (`tickets`.`workflow_id` = `tickets_workflow`.`id`)
LEFT JOIN `products` AS `tickets_product` ON (`tickets`.`product_id` = `tickets_product`.`id`)
LEFT JOIN `people` AS `tickets_person` ON (`tickets`.`person_id` = `tickets_person`.`id`)
LEFT JOIN `people_emails` AS `tickets_person_email` ON (`tickets`.`person_email_id` = `tickets_person_email`.`id`)
LEFT JOIN `people` AS `tickets_agent` ON (`tickets`.`agent_id` = `tickets_agent`.`id`)
LEFT JOIN `agent_teams` AS `tickets_agent_team` ON (`tickets`.`agent_team_id` = `tickets_agent_team`.`id`)
LEFT JOIN `organizations` AS `tickets_organization` ON (`tickets`.`organization_id` = `tickets_organization`.`id`)
LEFT JOIN `chat_conversations` AS `tickets_linked_chat` ON (`tickets`.`linked_chat_id` = `tickets_linked_chat`.`id`)
LEFT JOIN `email_accounts` AS `tickets_email_account` ON (`tickets`.`email_account_id` = `tickets_email_account`.`id`)
LIMIT 2500
SQL
        );
    }

    public function test_exists()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.ref
FROM tickets
WHERE EXISTS (
    SELECT people.id
    FROM people
    WHERE people.id = tickets.person.id
)
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`ref`
FROM `tickets`
WHERE  EXISTS (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `people`.`id`, `tickets_person`.`id`
FROM `people`
LEFT JOIN `tickets` AS `tickets_person` ON (`tickets`.`person_id` = `tickets_person`.`id`)
WHERE (`people`.`id` = `tickets_person`.`id`)
LIMIT 2500)
LIMIT 2500
SQL
        );
    }

    public function test_not_exists()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.ref
FROM tickets
WHERE NOT EXISTS (
    SELECT people.id
    FROM people
    WHERE people.id = tickets.person.id
)
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`ref`
FROM `tickets`
WHERE (NOT  EXISTS (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `people`.`id`, `tickets_person`.`id`
FROM `people`
LEFT JOIN `tickets` AS `tickets_person` ON (`tickets`.`person_id` = `tickets_person`.`id`)
WHERE (`people`.`id` = `tickets_person`.`id`)
LIMIT 2500))
LIMIT 2500
SQL
        );
    }

    public function test_in()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.urgency
FROM tickets
WHERE tickets.urgency IN (1, 2, 3)
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`urgency`
FROM `tickets`
WHERE `tickets`.`urgency` IN (1, 2, 3)
LIMIT 2500
SQL
        );
    }

    public function test_select_subquery()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT (SELECT people.name FROM people WHERE people.id = tickets.person_id) AS person_name
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `people`.`name` FROM `people` WHERE (`people`.`id` = `tickets`.`person_id`) LIMIT 2500)
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_select_func_subquery()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT CONCAT(DPQL_COUNT() * 100 / (SELECT people.id FROM people WHERE people.id = tickets.person_id), '%')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ CONCAT(((COUNT(*) * 100) / (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `people`.`id` FROM `people` WHERE (`people`.`id` = `tickets`.`person_id`) LIMIT 2500)), '%')
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_from_subquery()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT people.id FROM (SELECT people.id FROM people) AS people
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `people`.`id`
FROM (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `people`.`id` FROM `people` LIMIT 2500) AS people
LIMIT 2500
SQL
        );
    }

    public function test_where_in_subquery()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.ref
FROM tickets
WHERE tickets.ref IN (
    SELECT tickets.ref
    FROM tickets
    WHERE tickets.ref = 'AAAA-%'
)
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`ref`
FROM `tickets`
WHERE `tickets`.`ref` IN (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`ref`
FROM `tickets`
WHERE (`tickets`.`ref` = 'AAAA-%')
LIMIT 2500)
LIMIT 2500
SQL
        );
    }

    public function test_where_not_in_subquery()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.ref
FROM tickets
WHERE tickets.ref NOT IN (
    SELECT tickets.ref
    FROM tickets
    WHERE tickets.ref = 'AAAA-%'
)
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`ref`
FROM `tickets`
WHERE `tickets`.`ref` NOT IN (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`ref`
FROM `tickets`
WHERE (`tickets`.`ref` = 'AAAA-%')
LIMIT 2500)
LIMIT 2500
SQL
        );
    }

    public function test_like()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.ref
FROM tickets
WHERE tickets.ref LIKE 'AAAA-%'
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`ref`
FROM `tickets`
WHERE `tickets`.`ref` LIKE 'AAAA-%'
LIMIT 2500
SQL
        );
    }

    public function test_null_value()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.ref
FROM tickets
WHERE tickets.agent_id = NULL
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`ref`
FROM `tickets`
WHERE (`tickets`.`agent_id` IS NULL)
LIMIT 2500
SQL
        );
    }

    public function test_number()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.urgency
FROM tickets
WHERE tickets.urgency > 1
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`urgency`
FROM `tickets`
WHERE (`tickets`.`urgency` > 1)
LIMIT 2500
SQL
        );
    }

    public function test_order_dir()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.urgency
FROM tickets
ORDER BY tickets.urgency DESC
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`urgency`
FROM `tickets`
ORDER BY `tickets`.`urgency` DESC
LIMIT 2500
SQL
        );
    }

    public function test_parentheses_and_binary_logical()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.urgency
FROM tickets
WHERE (tickets.urgency > 1 AND tickets.urgency < 3) OR (tickets.urgency > 8 AND tickets.urgency < 10)
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`urgency`
FROM `tickets`
WHERE (((`tickets`.`urgency` > 1) AND (`tickets`.`urgency` < 3)) OR ((`tickets`.`urgency` > 8) AND (`tickets`.`urgency` < 10)))
LIMIT 2500
SQL
        );
    }

    public function test_regex()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.ref
FROM tickets
WHERE tickets.ref REGEXP 'AAAA-%'
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`ref`
FROM `tickets`
WHERE `tickets`.`ref` REGEXP 'AAAA-%'
LIMIT 2500
SQL
        );
    }

    public function test_string_part()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.ref
FROM tickets
WHERE tickets.ref = 'AAAA'
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`ref`
FROM `tickets`
WHERE (`tickets`.`ref` = 'AAAA')
LIMIT 2500
SQL
        );
    }

    public function test_unary_operator()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT -tickets.urgency
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ (-`tickets`.`urgency`)
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_simple_union()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.id
FROM (
    (SELECT tickets.id FROM tickets)
    UNION
    (SELECT tickets.id FROM tickets)
) as tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id`
FROM (
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
  UNION DISTINCT
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
) AS tickets
LIMIT 2500
SQL
        );
    }

    public function test_simple_distinct_union()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.id
FROM (
    (SELECT tickets.id FROM tickets)
    UNION DISTINCT
    (SELECT tickets.id FROM tickets)
) as tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id`
FROM (
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
  UNION DISTINCT
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
) AS tickets
LIMIT 2500
SQL
        );
    }

    public function test_simple_all_union()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.id
FROM (
    (SELECT tickets.id FROM tickets)
    UNION ALL
    (SELECT tickets.id FROM tickets)
) as tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id`
FROM (
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
  UNION ALL
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
) AS tickets
LIMIT 2500
SQL
        );
    }

    public function test_complex_union()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.id
FROM (
    (SELECT tickets.id FROM tickets)
    UNION
    (SELECT tickets.id FROM tickets)
    UNION DISTINCT
    (SELECT tickets.id FROM tickets)
    UNION
    (SELECT tickets.id FROM tickets)
    UNION ALL
    (SELECT tickets.id FROM tickets)
) as tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM (
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
  UNION DISTINCT
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
  UNION DISTINCT
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
  UNION DISTINCT
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
  UNION ALL
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
) AS tickets LIMIT 2500
SQL
        );
    }

    public function test_union_from_various_tables()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.id
FROM (
    (SELECT tickets.id FROM tickets)
    UNION
    (SELECT people.id FROM people)
) as tickets
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id`
FROM (
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `tickets`.`id` FROM `tickets` LIMIT 2500)
  UNION DISTINCT
  (SELECT /*+ MAX_EXECUTION_TIME(30000) */ `people`.`id` FROM `people` LIMIT 2500)
) AS tickets
LIMIT 2500
SQL
        );
    }

    /**
     * @expectedException \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     * @expectedExceptionMessage Alias people not found
     */
    public function test_unknown_alias()
    {
        $this->compiler->compile(
            <<<'DPQL'
SELECT people.id
FROM (
    (SELECT tickets.id FROM tickets)
    UNION
    (SELECT people.id FROM people)
) as tickets
DPQL
            , []);
    }

    /**
     * @expectedException \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     * @expectedExceptionMessage Unable to split UNION query part
     */
    public function test_not_allowed_split_by_in_subquery()
    {
        $this->compiler->compile(
            <<<'DPQL'
SELECT tickets.id
FROM (
    (SELECT tickets.id FROM tickets SPLIT BY tickets.agent)
    UNION
    (SELECT people.id FROM people)
) as tickets
DPQL
            , []);
    }

    // as we made a change in dpql datetime columnss we're using Y-m-d H:i:s format for every query we run.
    public function test_came_case_props()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_COUNT(), snippets.id FROM snippets WHERE snippets.date_created > '2018-01-25'
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ COUNT(*), `snippets`.`id`
FROM `snippets`
WHERE (`snippets`.`date_created` > '2018-01-25 00:00:00')
LIMIT 2500
SQL
        );
    }

    public function test_sub_select()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT
    DPQL_FORMAT(
	(
    	 (SELECT DPQL_COUNT() FROM ticket_feedback WHERE ticket_feedback.rating = 1 AND ticket_feedback.date_created = ${date})
    	  / 
    	 (SELECT DPQL_COUNT() FROM ticket_feedback WHERE ticket_feedback.date_created = ${date})
    	),
    'percent', 0)
    AS 'stat_value',
'satisfied users' as 'stat_description'
FROM ticket_feedback
WHERE ticket_feedback.date_created = ${date}
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ 
((SELECT /*+ MAX_EXECUTION_TIME(30000) */ COUNT(*) FROM `ticket_feedback` WHERE ((`ticket_feedback`.`rating` = 1) AND (`ticket_feedback`.`date_created` = 'date')) LIMIT 2500) 
  / (SELECT /*+ MAX_EXECUTION_TIME(30000) */ COUNT(*) FROM `ticket_feedback` WHERE (`ticket_feedback`.`date_created` = 'date') LIMIT 2500)), 'satisfied users' 
FROM `ticket_feedback` 
WHERE (`ticket_feedback`.`date_created` = 'date') 
LIMIT 2500
SQL
        );
    }

    public function test_edit_compiler_mode()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_COUNT(), snippets.id FROM snippets WHERE snippets.date_created > '2018-01-25'
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ COUNT(*), `snippets`.`id`
FROM `snippets`
WHERE (`snippets`.`date_created` > '2018-01-25')
LIMIT 2500
SQL
            ,
            DpqlContextStorage::MODE_EDIT
        );
    }
}
