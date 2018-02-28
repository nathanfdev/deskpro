<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\ReportBundle\Dpql2;

/**
 * Class CompilerFuncTest.
 */
class CompilerFuncTest extends AbstractCompilerTest
{
    public function test_alias()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_ALIAS(tickets.subject, 'Ticket Subject')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT `tickets`.`subject`
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_count()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_COUNT()
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT COUNT(*)
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_count_distinct()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_COUNT_DISTINCT(tickets.subject)
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT COUNT(DISTINCT `tickets`.`subject`)
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_cur_date()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_CURDATE()
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT DATE(UTC_TIMESTAMP())
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_cur_time()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_CURTIME()
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT TIME(UTC_TIMESTAMP())
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_date()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_DATE('2017-12-22 12:56:00')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT DATE('2017-12-22 12:56:00')
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_date_offset_group()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_DATE_OFFSET_GROUP(tickets.date_created, tickets.date_created)
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) IS NULL, 0,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 900, 900,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 1800, 1800,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 3600, 3600,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 7200, 7200,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 14400, 14400,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 43200, 43200,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 86400, 86400,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 172800, 172800,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 345600, 345600,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 604800, 604800,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 1209600, 1209600,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 2419200, 2419200,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 5270400, 5270400,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 7862400, 7862400,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 15724800, 15724800,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 31536000, 31536000,
    IF(UNIX_TIMESTAMP(`tickets`.`date_created`) - UNIX_TIMESTAMP(`tickets`.`date_created`) < 63072000, 63072000, 630720000))))))))))))))))))
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_day_name()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_DAYOFWEEK('2017-12-22 12:56:00')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT DAYOFWEEK('2017-12-22 12:56:00')
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_day_of_month()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_DAYOFMONTH('2017-12-22 12:56:00')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT DAYOFMONTH('2017-12-22 12:56:00')
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_day_of_week()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_DAYOFWEEK('2017-12-22 12:56:00')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT DAYOFWEEK('2017-12-22 12:56:00')
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_format()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_FORMAT(tickets.date_created, 'date')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT `tickets`.`date_created`
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_hierarchy()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_COUNT() AS 'Total Tickets'
FROM tickets
GROUP BY tickets.urgency
WITH ROLLUP
DPQL
            ,
            <<<'SQL'
SELECT COUNT(*), `tickets`.`urgency`
FROM `tickets`
GROUP BY `tickets`.`urgency`
ORDER BY `tickets`.`urgency`
LIMIT 2500
SQL
        );
    }

    public function test_hour()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_HOUR('2017-12-22 12:56:00')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT HOUR('2017-12-22 12:56:00')
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_link()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_LINK(tickets.id, 'ticket')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT `tickets`.`id`
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_matrix()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_COUNT() AS 'Total Tickets'
FROM tickets
GROUP BY
DPQL_MATRIX(DPQL_ALIAS(DPQL_DAYOFMONTH(tickets.date_created), 'Day of Month Created'),
DPQL_ALIAS(DPQL_STACK_GROUP(tickets.department,COALESCE(tickets.department.parent.title,
 tickets.department.title)),'Department'))
DPQL
            ,
            <<<'SQL'
SELECT COUNT(*), COALESCE(`tickets_department_parent`.`title`, `tickets_department`.`title`), DAYOFMONTH(`tickets`.`date_created`), `tickets_department`.`title`, `tickets_department`.`id`
FROM `tickets`
LEFT JOIN `departments` AS `tickets_department` ON (`tickets`.`department_id` = `tickets_department`.`id`)
LEFT JOIN `departments` AS `tickets_department_parent` ON (`tickets_department`.`parent_id` = `tickets_department_parent`.`id`)
GROUP BY DAYOFMONTH(`tickets`.`date_created`), `tickets_department`.`id`
ORDER BY DAYOFMONTH(`tickets`.`date_created`), `tickets_department`.`title`
LIMIT 2500
SQL
        );
    }

    public function test_minute()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_MINUTE('2017-12-22 12:56:00')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT MINUTE('2017-12-22 12:56:00')
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_month()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_MONTH('2017-12-22 12:56:00')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT MONTH('2017-12-22 12:56:00')
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_month_name()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_MONTH('2017-12-22 12:56:00')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT MONTH('2017-12-22 12:56:00')
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_now()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_NOW()
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT (UTC_TIMESTAMP())
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_obj_lang()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_OBJ_LANG(ticket_object_use_logs.snippet.category.id, 'text_snippet_categories') AS 'Snippet Category'
FROM ticket_object_use_logs
DPQL
        ,
            <<<'SQL'
SELECT `ticket_object_use_logs_snippet_category`.`id`
FROM `ticket_object_use_logs`
LEFT JOIN `text_snippets` AS `ticket_object_use_logs_snippet` ON (`ticket_object_use_logs`.`snippet_id` = `ticket_object_use_logs_snippet`.`id`)
LEFT JOIN `text_snippet_categories` AS `ticket_object_use_logs_snippet_category` ON (`ticket_object_use_logs_snippet`.`category_id` = `ticket_object_use_logs_snippet_category`.`id`)
LIMIT 2500
SQL
        );
    }

    public function test_percent()
    {
        $this->assertDpqlQuery(
                <<<'DPQL'
SELECT DPQL_PERCENT(tickets.total_to_first_reply)
FROM tickets
DPQL
                ,
                <<<'SQL'
SELECT IF(COUNT(*) > 0, (SUM(IF(`tickets`.`total_to_first_reply`, 1, 0)) / COUNT(*)) * 100, 0)
FROM `tickets` LIMIT 2500
SQL
            );
    }

    public function test_printable()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.ref
FROM tickets
GROUP BY DPQL_PRINT(tickets.subject, tickets.id)
DPQL
            ,
            <<<'SQL'
SELECT `tickets`.`ref`, `tickets`.`id`, `tickets`.`subject` FROM `tickets`
GROUP BY `tickets`.`subject`
ORDER BY `tickets`.`id`
LIMIT 2500
SQL
        );
    }

    public function test_sql_pass()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT FROM_UNIXTIME(DPQL_NOW())
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT FROM_UNIXTIME((UTC_TIMESTAMP()))
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_stack_group()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_COUNT() AS 'Total Tickets'
FROM tickets
GROUP BY DPQL_STACK_GROUP(tickets.department, COALESCE(tickets.department.parent.title, tickets.department.title))
DPQL
            ,
            <<<'SQL'
SELECT COUNT(*), COALESCE(`tickets_department_parent`.`title`, `tickets_department`.`title`), `tickets_department`.`title`, `tickets_department`.`id`
FROM `tickets`
LEFT JOIN `departments` AS `tickets_department` ON (`tickets`.`department_id` = `tickets_department`.`id`)
LEFT JOIN `departments` AS `tickets_department_parent` ON (`tickets_department`.`parent_id` = `tickets_department_parent`.`id`)
GROUP BY `tickets_department`.`id`
ORDER BY `tickets_department`.`title`
LIMIT 2500
SQL
        );
    }

    public function test_time_length()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_TOTAL(DPQL_TIME_LENGTH(ticket_charges.charge_time)) AS 'Time'
FROM ticket_charges
DPQL
            ,
            <<<'SQL'
SELECT `ticket_charges`.`charge_time`
FROM `ticket_charges`
LIMIT 2500
SQL
        );
    }

    public function test_total()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_TOTAL(tickets.id)
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT `tickets`.`id`
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_to_utc()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_TO_UTC(tickets.date_created)
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT (`tickets`.`date_created`)
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_utc()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_UTC(tickets.date_created)
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT `tickets`.`date_created`
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_x_y()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT tickets.ref
FROM tickets
GROUP BY DPQL_X(tickets.person_id), DPQL_Y(tickets.agent_id)
DPQL
            ,
            <<<'SQL'
SELECT `tickets`.`ref`, `tickets`.`person_id`, `tickets`.`agent_id`
FROM `tickets`
GROUP BY `tickets`.`person_id`, `tickets`.`agent_id`
ORDER BY `tickets`.`person_id`, `tickets`.`agent_id`
LIMIT 2500
SQL
        );
    }

    public function test_year()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_YEAR('2017-12-22 12:56:00')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT YEAR('2017-12-22 12:56:00')
FROM `tickets`
LIMIT 2500
SQL
        );
    }

    public function test_json_extract()
    {
        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_JSON_EXTRACT(tickets.id, '$.id')
FROM tickets
DPQL
            ,
            <<<'SQL'
SELECT `tickets`.`id`
FROM `tickets`
LIMIT 2500
SQL
        );
    }
}
