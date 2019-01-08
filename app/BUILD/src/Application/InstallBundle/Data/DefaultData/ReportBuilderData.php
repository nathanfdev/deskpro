<?php

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\ReportBuilder;
use Application\DeskPRO\EntityRepository\ReportBuilder as ReportBuilderRepository;

class ReportBuilderData extends AbstractDefaultData
{
    private $data = [
        'article-views-date-x-grouped-date' => [
                'category'      => 'kb',
                'description'   => '',
                'display_order' => '40',
                'query'         => 'DISPLAY TABLE, LINE
SELECT COUNT() AS \'Views\'
FROM articles
WHERE articles.views.date_created = %1:DATE_GROUP%
GROUP BY ALIAS(DATE(articles.views.date_created), \'Date\')',
                'title' => 'Number of article views <1:date group, default: this_month> grouped by date <chart:line>',
            ],

        'average-chat-length-chats-created-group-x' => [
                'category'      => 'chat',
                'description'   => '',
                'display_order' => '30',
                'query'         => 'DISPLAY TABLE, BAR
SELECT AVG(chat_conversations.total_to_ended) / 60 AS \'Average Length (Minutes)\'
FROM chat_conversations
WHERE chat_conversations.date_created = %1:DATE_GROUP% AND chat_conversations.is_agent = 0 AND chat_conversations.status = \'ended\' AND chat_conversations.total_to_ended > 0
GROUP BY %2:FIELD_GROUP:chats:chat_conversations%',
                'title' => 'Average chat length for chats created <1:date group, default: this_month> grouped by <2:field group:chats, default: agent>',
            ],

        'average-time-first-re-tickets-created-date-group-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '120',
                'query'         => 'DISPLAY TABLE, BAR
SELECT AVG(UNIX_TIMESTAMP(tickets.date_first_agent_reply) - UNIX_TIMESTAMP(tickets.date_created)) / (60 * 60) AS \'Average Time (Hours)\', COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = %1:DATE_GROUP% AND tickets.date_first_agent_reply <> NULL
GROUP BY %2:FIELD_GROUP:tickets%',
                'title' => 'Average time to first response in tickets created <1:date group, default: this_month> grouped by <2:field group:tickets> <chart:bar>',
            ],

        'average-time-resolve-tickets-date-group-by-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '100',
                'query'         => 'DISPLAY TABLE, BAR
SELECT AVG(UNIX_TIMESTAMP(tickets.date_resolved) - UNIX_TIMESTAMP(tickets.date_created)) / (60 * 60) AS \'Average Time (Hours)\', COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = %1:DATE_GROUP% AND tickets.date_resolved <> NULL
GROUP BY %2:FIELD_GROUP:tickets%',
                'title' => 'Average time to resolve tickets <1:date group, default: this_month> grouped by <2:field group:tickets> <chart:bar>',
            ],

        'average-total-wait-tickets-resolve-date-group-by-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '110',
                'query'         => 'DISPLAY TABLE, BAR
SELECT AVG(tickets.total_user_waiting) / (60 * 60) AS \'Total Waiting Time (Hours)\', COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = %1:DATE_GROUP%
GROUP BY %2:FIELD_GROUP:tickets%',
                'title' => 'Average total waiting time for tickets resolved <1:date group, default: this_month> grouped by <2:field group:tickets> <chart:bar>',
            ],

        'feedback-views-date-x-grouped-date' => [
                'category'      => 'feedback',
                'description'   => '',
                'display_order' => '40',
                'query'         => 'DISPLAY TABLE, LINE
SELECT COUNT() AS \'Views\'
FROM feedback
WHERE feedback.views.date_created = %1:DATE_GROUP%
GROUP BY ALIAS(DATE(feedback.views.date_created), \'Date\')',
                'title' => 'Number of feedback views <1:date group, default: this_month> grouped by date <chart:line>',
            ],

        'most-active-tickets-status-created-date' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '210',
                'query'         => 'DISPLAY TABLE
SELECT tickets_messages.ticket.subject, COUNT() AS \'Messages\', tickets_messages.ticket.person, tickets_messages.ticket.department, tickets_messages.ticket.date_created, tickets_messages.ticket.agent
FROM tickets_messages
WHERE %1:STATUS_GROUP:tickets:tickets_messages.ticket% AND tickets_messages.ticket.date_created = %2:DATE_GROUP%
GROUP BY tickets_messages.ticket.id
ORDER BY COUNT() DESC
LIMIT 100',
                'title' => 'Most active tickets <1:status group:tickets, default: awaiting_agent> created <2:date group, default: this_month>',
            ],

        'most-popular-email-domains-ticket-usage' => [
                'category'      => 'person',
                'description'   => '',
                'display_order' => '0',
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Uses\'
FROM tickets
GROUP BY tickets.person_email.email_domain
ORDER BY COUNT() DESC
LIMIT 100',
                'title' => 'Most popular email domains by ticket usage',
            ],

        'most-popular-people-email-domains' => [
                'category'      => 'person',
                'description'   => '',
                'display_order' => '0',
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total People\'
FROM people
GROUP BY people.primary_email.email_domain
ORDER BY COUNT() DESC
LIMIT 100',
                'title' => 'Most popular primary email domains',
            ],

        'number-article-com-created-date-group-by-article' => [
                'category'      => 'kb',
                'description'   => '',
                'display_order' => '30',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Comments Created\'
FROM article_comments
WHERE article_comments.date_created = %1:DATE_GROUP%
GROUP BY %2:FIELD_GROUP:articles:article_comments.article%',
                'title' => 'Number of article comments created <1:date group, default: this_month> grouped by article <2:field group:articles, default: person> <chart:bar>',
            ],

        'number-article-comments-created-date-group-by-x' => [
                'category'      => 'kb',
                'description'   => '',
                'display_order' => '20',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Comments Created\'
FROM article_comments
WHERE article_comments.date_created = %1:DATE_GROUP%
GROUP BY %2:FIELD_GROUP:article_comments%',
                'title' => 'Number of article comments created <1:date group, default: this_month> grouped by <2:field group:article_comments, default: none> <chart:bar>',
            ],

        'number-articles-created-date-group-by-x' => [
                'category'      => 'kb',
                'description'   => '',
                'display_order' => '10',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Entries Created\'
FROM articles
WHERE articles.date_created = %1:DATE_GROUP%
GROUP BY %2:FIELD_GROUP:articles%',
                'title' => 'Number of articles created <1:date group, default: this_month> grouped by <2:field group:articles, default: person> <chart:bar>',
            ],

        'number-chats-created-date-grouped-by-x' => [
                'category'      => 'chat',
                'description'   => '',
                'display_order' => '10',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Chats Created\'
FROM chat_conversations
WHERE chat_conversations.date_created = %1:DATE_GROUP% AND chat_conversations.is_agent = 0
GROUP BY %2:FIELD_GROUP:chats:chat_conversations%',
                'title' => 'Number of chats created <1:date group, default: this_month> grouped by <2:field group:chats, default: agent> <chart:bar>',
            ],

        'number-chats-missed-date-grouped-by-x' => [
                'category'      => 'chat',
                'description'   => '',
                'display_order' => '20',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Chats Created\'
FROM chat_conversations
WHERE chat_conversations.date_created = %1:DATE_GROUP% AND chat_conversations.is_agent = 0 AND chat_conversations.agent_id = NULL
GROUP BY %2:FIELD_GROUP:chats:chat_conversations%',
                'title' => 'Number of chats missed <1:date group, default: this_month> grouped by <2:field group:chats, default: department> <chart:bar>',
            ],

        'number-feedback-com-created-date-group-by-feedback' => [
                'category'      => 'feedback',
                'description'   => '',
                'display_order' => '30',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Comments Created\'
FROM feedback_comments
WHERE feedback_comments.date_created = %1:DATE_GROUP%
GROUP BY %2:FIELD_GROUP:feedback:feedback_comments.feedback%',
                'title' => 'Number of feedback comments created <1:date group, default: this_month> grouped by feedback <2:field group:feedback, default: type> <chart:bar>',
            ],

        'number-feedback-comments-created-date-group-by-x' => [
                'category'      => 'feedback',
                'description'   => '',
                'display_order' => '20',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Comments Created\'
FROM feedback_comments
WHERE feedback_comments.date_created = %1:DATE_GROUP%
GROUP BY %2:FIELD_GROUP:feedback_comments%',
                'title' => 'Number of feedback comments created <1:date group, default: this_month> grouped by <2:field group:feedback_comments, default: none> <chart:bar>',
            ],

        'number-feedback-created-date-group-by-x' => [
                'category'      => 'feedback',
                'description'   => '',
                'display_order' => '10',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Entries Created\'
FROM feedback
WHERE feedback.date_created = %1:DATE_GROUP%
GROUP BY %2:FIELD_GROUP:feedback%',
                'title' => 'Number of feedback entries created <1:date group, default: this_month> grouped by <2:field group:feedback, default: type> <chart:bar>',
            ],

        'number-feedback-votes-submitted-date-x-group-y' => [
                'category'      => 'feedback',
                'description'   => '',
                'display_order' => '60',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Ratings\'
FROM feedback
WHERE feedback.ratings.date_created = %1:DATE_GROUP%
GROUP BY %2:FIELD_GROUP:feedback%',
                'title' => 'Number of feedback votes submitted <1:date group, default: this_month> grouped by <2:field group:feedback, default: type> <chart:bar>',
            ],

        'number-ticket-messages-written-agent-day' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '140',
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = %1:DATE_GROUP%
SPLIT BY tickets_messages.person
GROUP BY CONCAT(YEAR(tickets_messages.date_created), \'-\', LPAD(MONTHNAME(tickets_messages.date_created), 2, \'0\'), \'-\', LPAD(DAYOFMONTH(tickets_messages.date_created), 2, \'0\')) AS \'Period\'
ORDER BY tickets_messages.date_created',
                'title' => 'Number of ticket messages written <1:date group, default: this_year> per agent per [day]',
            ],

        'number-ticket-messages-written-agent-month' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '140',
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = %1:DATE_GROUP%
SPLIT BY tickets_messages.person
GROUP BY CONCAT(YEAR(tickets_messages.date_created), \'-\', LPAD(MONTHNAME(tickets_messages.date_created), 2, \'0\')) AS \'Period\'
ORDER BY tickets_messages.date_created',
                'title' => 'Number of ticket messages written <1:date group, default: this_year> per agent per [month]',
            ],

        'number-ticket-messages-written-agent-week' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '140',
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = %1:DATE_GROUP% 
SPLIT BY tickets_messages.person
GROUP BY CONCAT(\'Week \', WEEKOFYEAR(tickets_messages.date_created), \', \', YEAR(tickets_messages.date_created)) AS \'Period\'
ORDER BY tickets_messages.date_created',
                'title' => 'Number of ticket messages written <1:date group, default: this_year> per agent per [week]',
            ],

        'number-ticket-messages-written-agent-year' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '140',
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = %1:DATE_GROUP%
SPLIT BY tickets_messages.person
GROUP BY YEAR(tickets_messages.date_created) AS \'Period\'
ORDER BY YEAR(tickets_messages.date_created) DESC',
                'title' => 'Number of ticket messages written <1:date group, default: this_year> per agent per [year]',
            ],

        'number-tickets-created-date-grouped-by-date-and-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '15',
                'query'         => 'DISPLAY TABLE, AREA
SELECT COUNT() AS \'Tickets Created\'
FROM tickets
WHERE tickets.date_created = %1:DATE_GROUP%
GROUP BY ALIAS(DATE(tickets.date_created), \'Date Created\'), %2:FIELD_GROUP:tickets%',
                'title' => 'Number of tickets created <1:date group, default: this_month> grouped by date created & <2:field group:tickets, default: none> <chart:area>',
            ],

        'number-tickets-created-date-grouped-by-x-y' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '10',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = %1:DATE_GROUP%
GROUP BY MATRIX(%2:FIELD_GROUP:tickets%, %3:FIELD_GROUP:tickets%)',
                'title' => 'Number of tickets created <1:date group, default: this_month> grouped by <2:field group:tickets, default: department> & <3:field group:tickets, default: agent> <chart:bar>',
            ],

        'number-tickets-created-date-grouped-first-agent-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '90',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = %1:DATE_GROUP%
GROUP BY MATRIX(ALIAS(DATE_OFFSET_GROUP(tickets.date_created, tickets.date_first_agent_reply), \'Time Waiting\'), %2:FIELD_GROUP:tickets%)',
                'title' => 'Number of tickets created <1:date group, default: this_month> grouped by first agent response time & <2:field group:tickets, default: none> <chart:bar>',
            ],

        'number-tickets-resolved-date-grouped-time-res-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '80',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = %1:DATE_GROUP%
GROUP BY MATRIX(ALIAS(DATE_OFFSET_GROUP(tickets.date_resolved, tickets.date_created), \'Time To Resolve\'), %2:FIELD_GROUP:tickets%)',
                'title' => 'Number of tickets resolved <1:date group, default: this_month> grouped by time to resolution & <2:field group:tickets, default: none> <chart:bar>',
            ],

        'number-tickets-resolved-date-grouped-total-wait-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '70',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = %1:DATE_GROUP%
GROUP BY MATRIX(ALIAS(DATE_OFFSET_GROUP(tickets.total_user_waiting), \'Time Waiting\'), %2:FIELD_GROUP:tickets%)',
                'title' => 'Number of tickets resolved <1:date group, default: this_month> grouped by total waiting time & <2:field group:tickets, default: none> <chart:bar>',
            ],

        'number-tickets-resolved-date-grouped-x-y' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '30',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_resolved = %1:DATE_GROUP% AND tickets.status IN (\'resolved\', \'archived\')
GROUP BY MATRIX(%2:FIELD_GROUP:tickets%, %3:FIELD_GROUP:tickets%)',
                'title' => 'Number of tickets resolved <1:date group, default: this_month> grouped by <2:field group:tickets, default: department> & <3:field group:tickets, default: agent> <chart:bar>',
            ],

        'number-tickets-status-grouped-by-x-y' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '20',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE %1:STATUS_GROUP:tickets%
GROUP BY MATRIX(%2:FIELD_GROUP:tickets%, %3:FIELD_GROUP:tickets%)',
                'title' => 'Number of tickets <1:status group:tickets, default: awaiting_agent> grouped by <2:field group:tickets, default: department> & <3:field group:tickets, default: agent> <chart:bar>',
            ],

        'number-tickets-wait-agent-grouped-time-wait-ag-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '50',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\')
GROUP BY MATRIX(ALIAS(DATE_OFFSET_GROUP(NOW(), tickets.date_user_waiting), \'Time Waiting\'), %1:FIELD_GROUP:tickets%)',
                'title' => 'Number of tickets awaiting agent grouped by time awaiting agent and <1:field group:tickets, default: none> <chart:bar>',
            ],

        'number-tickets-wait-agent-grouped-total-wait-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '60',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\')
GROUP BY MATRIX(ALIAS(DATE_OFFSET_GROUP(tickets.total_user_waiting), \'Time Waiting\'), %1:FIELD_GROUP:tickets%)',
                'title' => 'Number of tickets awaiting agent grouped by total waiting time and <1:field group:tickets, default: none> <chart:bar>',
            ],

        'number-views-per-article-date-x' => [
                'category'      => 'kb',
                'description'   => '',
                'display_order' => '50',
                'query'         => 'DISPLAY TABLE
SELECT articles.title, COUNT() AS \'Views\'
FROM articles
WHERE articles.views.date_created = %1:DATE_GROUP%
GROUP BY articles.id
ORDER BY COUNT() DESC',
                'title' => 'Number of views per article <1:date group, default: this_month>',
            ],

        'number-views-per-feedback-date-x' => [
                'category'      => 'feedback',
                'description'   => '',
                'display_order' => '50',
                'query'         => 'DISPLAY TABLE
SELECT feedback.title, COUNT() AS \'Views\'
FROM feedback
WHERE feedback.views.date_created = %1:DATE_GROUP%
GROUP BY feedback.id
ORDER BY COUNT() DESC',
                'title' => 'Number of views per feedback entry <1:date group, default: this_month>',
            ],

        'organizations-longest-total--wait' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '200',
                'query'         => 'DISPLAY TABLE
SELECT SUM(tickets.total_user_waiting) / (60 * 60) AS \'Total Wait (Hours)\', COUNT() AS \'Total Tickets\', AVG(tickets.total_user_waiting) / (60 * 60) AS \'Average Wait (Hours)\'
FROM tickets
GROUP BY tickets.organization
ORDER BY SUM(tickets.total_user_waiting) DESC
LIMIT 100',
                'title' => '[Organizations] with longest total waiting time',
            ],

        'organizations-longest-total-first-reply-wait' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '190',
                'query'         => 'DISPLAY TABLE
SELECT SUM(tickets.total_to_first_reply) / (60 * 60) AS \'Total Wait (Hours)\', COUNT() AS \'Total Tickets\', AVG(tickets.total_to_first_reply) / (60 * 60) AS \'Average Wait (Hours)\'
FROM tickets
GROUP BY tickets.organization
ORDER BY SUM(tickets.total_to_first_reply) DESC
LIMIT 100',
                'title' => '[Organizations] with longest total first reply waiting time',
            ],

        'people-longest-total--wait' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '200',
                'query'         => 'DISPLAY TABLE
SELECT SUM(tickets.total_user_waiting) / (60 * 60) AS \'Total Wait (Hours)\', COUNT() AS \'Total Tickets\', AVG(tickets.total_user_waiting) / (60 * 60) AS \'Average Wait (Hours)\'
FROM tickets
GROUP BY tickets.person
ORDER BY SUM(tickets.total_user_waiting) DESC
LIMIT 100',
                'title' => '[People] with longest total waiting time',
            ],

        'people-longest-total-first-reply-wait' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '190',
                'query'         => 'DISPLAY TABLE
SELECT SUM(tickets.total_to_first_reply) / (60 * 60) AS \'Total Wait (Hours)\', COUNT() AS \'Total Tickets\', AVG(tickets.total_to_first_reply) / (60 * 60) AS \'Average Wait (Hours)\'
FROM tickets
GROUP BY tickets.person
ORDER BY SUM(tickets.total_to_first_reply) DESC
LIMIT 100',
                'title' => '[People] with longest total first reply waiting time',
            ],

        'percent-ticket-create-date-resolved-24hour-group-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '131',
                'query'         => 'DISPLAY TABLE, BAR
SELECT PERCENT(tickets.date_resolved <> NULL AND (UNIX_TIMESTAMP(tickets.date_resolved) - UNIX_TIMESTAMP(tickets.date_created)) < 24 * 60 * 60) AS \'Percentage\'
FROM tickets
WHERE tickets.date_created = %1:DATE_GROUP%
GROUP BY %2:FIELD_GROUP:tickets%',
                'title' => 'Percentage of tickets created <1:date group, default: this_month> resolved within 24 hours, grouped by <2:field group:tickets> <chart:bar>',
            ],

        'percent-tickets-created-date-replied-hour-group-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '130',
                'query'         => 'DISPLAY TABLE, BAR
SELECT PERCENT(tickets.total_to_first_reply < 3600) AS \'Percentage\'
FROM tickets
WHERE tickets.date_created = %1:DATE_GROUP% AND tickets.total_to_first_reply > 0
GROUP BY %2:FIELD_GROUP:tickets%',
                'title' => 'Percentage of tickets created <1:date group, default: this_month> replied to within an hour, grouped by <2:field group:tickets> <chart:bar>',
            ],

        'percent-tickets-created-date-res-1-agent-group-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '135',
                'query'         => 'DISPLAY TABLE, BAR
SELECT PERCENT(tickets.count_agent_replies = 1) AS \'Percentage\'
FROM tickets
WHERE tickets.date_created = %1:DATE_GROUP% AND tickets.date_resolved <> NULL AND tickets.count_agent_replies > 0
GROUP BY %2:FIELD_GROUP:tickets%',
                'title' => 'Percentage of tickets created <1:date group, default: this_month> resolved by first response, grouped by <2:field group:tickets, default: agent> <chart:bar>',
            ],

        'sla-date-groupby-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '96',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = %1:DATE_GROUP% AND tickets.ticket_slas.sla_status IN (\'ok\', \'warning\', \'fail\')
GROUP BY %2:FIELD_GROUP:tickets%, tickets.ticket_slas.sla_status',
                'title' => 'SLA Statuses for tickets created <1:date group, default: this_month> grouped by <2:field group:tickets, default: agent> <chart:bar>',
            ],

        'sla-status-date-splitby-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '95',
                'query'         => 'DISPLAY TABLE, PIE
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = %1:DATE_GROUP% AND tickets.ticket_slas.sla_status IN (\'ok\', \'warning\', \'fail\')
SPLIT BY %2:FIELD_GROUP:tickets%
GROUP BY tickets.ticket_slas.sla_status',
                'title' => 'SLA Statuses for tickets created <1:date group, default: this_month> split by <2:field group:tickets, default:sla> <chart:pie>',
            ],

        'tickets-awaiting-agent-split-by-field-ordered-by-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '40',
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent
FROM tickets
WHERE tickets.status = IN (\'awaiting_agent\', \'pending\')
SPLIT BY %1:FIELD_GROUP:tickets%
ORDER BY %2:ORDER_GROUP:tickets%
LIMIT 100',
                'title' => 'Tickets awaiting agent split by <1:field group:tickets> ordered by <2:order group:tickets>',
            ],

        'tickets-created-date-grouped-labels' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '170',
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.labels.label <> NULL AND tickets.date_created = %1:DATE_GROUP%
GROUP BY tickets.labels',
                'title' => 'Tickets created <1:date group, default: this_month> grouped by labels',
            ],

        'tickets-resolved-date-grouped-by-agent-resolving' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '160',
                'query'         => 'DISPLAY TABLE
SELECT COUNT_DISTINCT(tickets_logs.ticket_id) AS \'Tickets Resolved\'
FROM tickets_logs
WHERE tickets_logs.action_type = \'changed_status\' AND tickets_logs.id_after = 200 AND tickets_logs.ticket.status IN (\'resolved\', \'archived\') AND tickets_logs.date_created = %1:DATE_GROUP%
GROUP BY ALIAS(IF(tickets_logs.person.is_agent, tickets_logs.person, \'Non-Agent\'), \'Person\')
ORDER BY @\'Tickets Resolved\' DESC
LIMIT 100',
                'title' => 'Tickets resolved <1:date group, default: this_month> grouped by agent resolving ticket',
            ],

        'tickets-split-by-labels' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '180',
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent
FROM tickets
WHERE tickets.labels.label <> NULL
SPLIT BY tickets.labels
ORDER BY tickets.date_created DESC
LIMIT 100',
                'title' => 'Tickets split by labels',
            ],

        'tickets-unresolved-split-field-ordered-x' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '41',
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\')
SPLIT BY %1:FIELD_GROUP:tickets%
ORDER BY %2:ORDER_GROUP:tickets%
LIMIT 100',
                'title' => 'Tickets unresolved split by <1:field group:tickets> ordered by <2:order group:tickets>',
            ],

        'total-tickets-unresolved-after-week' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '220',
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total\', COUNT(tickets.status IN (\'awaiting_agent\', \'pending\')) AS \'Total Awaiting Agent\', COUNT(tickets.status = \'awaiting_user\') AS \'Total Awaiting User\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\') AND tickets.date_created < %PAST_7_DAYS%',
                'title' => 'Total tickets unresolved after a week',
            ],

        'unresolved-high-priority-tickets' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '230',
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent, tickets.priority
FROM tickets
WHERE tickets.priority.priority = 1 AND tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\')
ORDER BY tickets.date_created
LIMIT 100',
                'title' => 'Unresolved [high priority] tickets',
            ],

        'unresolved-tickets-10-more-agent-replies' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '240',
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent, tickets.count_agent_replies AS \'Agent Replies\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\') AND tickets.count_agent_replies >= 10
ORDER BY tickets.date_created
LIMIT 100',
                'title' => 'Unresolved tickets with 10 or more agent replies',
            ],

        'unresolved-urgent-tickets' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '230',
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent, tickets.urgency
FROM tickets
WHERE tickets.urgency > 7 AND tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\')
ORDER BY tickets.date_created
LIMIT 100',
                'title' => 'Unresolved [urgent] tickets',
            ],

        'users-most-unresolved-tickets' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '150',
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\')
GROUP BY tickets.person
ORDER BY COUNT() DESC
LIMIT 100',
                'title' => 'Users with most unresolved tickets',
            ],

        'snippets-used-split-category' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '500',
                'query'         => 'DISPLAY TABLE
SELECT OBJ_LANG(ticket_object_use_logs.snippet.category.id, \'text_snippet_categories\') AS \'Snippet Category\', OBJ_LANG(ticket_object_use_logs.snippet.id, \'text_snippets\') AS \'Snippet\', COUNT() AS \'Uses\'
FROM ticket_object_use_logs
WHERE ticket_object_use_logs.snippet.id <> NULL AND ticket_object_use_logs.date_created = %1:DATE_GROUP%
SPLIT BY OBJ_LANG(ticket_object_use_logs.snippet.category.id, \'text_snippet_categories\')
GROUP BY ticket_object_use_logs.snippet.id
ORDER BY COUNT() DESC',
                'title' => 'Snippets used <1:date group, default: this_month> split by category',
            ],

        'snippets-used' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '500',
                'query'         => 'DISPLAY TABLE
SELECT OBJ_LANG(ticket_object_use_logs.snippet.category.id, \'text_snippet_categories\') AS \'Snippet Category\', OBJ_LANG(ticket_object_use_logs.snippet.id, \'text_snippets\') AS \'Snippet\', COUNT() AS \'Uses\'
FROM ticket_object_use_logs
WHERE ticket_object_use_logs.snippet.id <> NULL AND ticket_object_use_logs.date_created = %1:DATE_GROUP%
GROUP BY ticket_object_use_logs.snippet.id
ORDER BY COUNT() DESC',
                'title' => 'Snippets used <1:date group, default: this_month>',
            ],

        'snippet-cats-used' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '500',
                'query'         => 'DISPLAY TABLE
SELECT OBJ_LANG(ticket_object_use_logs.snippet.category.id, \'text_snippet_categories\') AS \'Snippet Category\', COUNT() AS \'Uses\'
FROM ticket_object_use_logs
WHERE ticket_object_use_logs.snippet.id <> NULL AND ticket_object_use_logs.date_created = %1:DATE_GROUP%
GROUP BY ticket_object_use_logs.snippet.category.id
ORDER BY COUNT() DESC',
                'title' => 'Snippet categories used <1:date group, default: this_month>',
            ],

        'macros-used' => [
                'category'      => 'ticket',
                'description'   => '',
                'display_order' => '500',
                'query'         => 'DISPLAY TABLE
SELECT ticket_object_use_logs.macro.title, COUNT() AS \'Uses\'
FROM ticket_object_use_logs
WHERE ticket_object_use_logs.macro.id <> NULL AND ticket_object_use_logs.date_created = %1:DATE_GROUP%
GROUP BY ticket_object_use_logs.macro.id
ORDER BY COUNT() DESC',
                'title' => 'Macros used <1:date group, default: this_month>',
            ],

    ];

    /**
     * Called during a fresh install.
     */
    public function runInstall()
    {
        /* @var ReportBuilderRepository $repo */
        $ids      = array_keys($this->data);
        $em       = $this->getEm();
        $schedule = $em->createQuery('
            SELECT r
            FROM DeskPRO:ReportBuilder r
            WHERE r.unique_key IS NOT NULL
        ')->execute();
        $updated = $existing = [];

        foreach ($schedule as $report) {
            /* @var ReportBuilder $report */
            $existing[] = $report->getUniqueKey();
            if (array_key_exists($report->getUniqueKey(), $this->data)) {
                $updated[] = $report->getUniqueKey();
                $data      = $this->data[$report->getUniqueKey()];
                $report
                    ->setTitle($data['title'])
                    ->setQuery($data['query'])
                    ->setCategory($data['category'])
                    ->setDisplayOrder($data['display_order'])
                    ->setDescription($data['description'])
                    ->setIsCustom(false);
                $em->persist($report);
            } else {
                $em->remove($report);
            }
        }

        $scheduledToInsert = array_diff($ids, $updated);

        foreach ($scheduledToInsert as $insert) {
            $report = new ReportBuilder();
            $data   = $this->data[$insert];
            $report
                ->setUniqueKey($insert)
                ->setTitle($data['title'])
                ->setQuery($data['query'])
                ->setCategory($data['category'])
                ->setDisplayOrder($data['display_order'])
                ->setDescription($data['description'])
                ->setIsCustom(false);
            $em->persist($report);
        }

        $em->flush();
    }

    /**
     * Called automatically during upgrades when the package has been installed before.
     * This is used to sync the database with any changes (e.g. adding new records or updating them).
     */
    public function runSync()
    {
        $this->runInstall();
    }

    /**
     * Called with the dp:reset-default-data command specifically. Usually the same as runSync.
     */
    public function runReset()
    {
        $this->runInstall();
    }
}
