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

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\EntityRepository\ReportWidget as ReportWidgetRepository;

class ReportWidgetData extends AbstractDefaultData
{
    private $data = [
        'tickets-awaiting-agent' => [
            'title'         => 'Count of tickets currently awaiting agent',
            'labels'        => 'tickets',
            'description'   => 'Tickets awaiting agent',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() as \'stat_value\', \'Tickets awaiting agent\' as \'stat_description\'
            FROM tickets WHERE tickets.status = \'awaiting_agent\'',
            'variables' => '[]',
        ],
        'agents-online' => [
            'title'         => 'Count of agents online right now',
            'labels'        => 'agents',
            'description'   => 'Agents are online',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() as \'stat_value\', \'Agents are online\' as \'stat_description\' 
FROM sessions WHERE sessions.person.is_agent = 1',
            'variables' => '[]',
        ],
        'tickets-created-x-date' => [
            'title'         => 'Count of tickets created ${date}',
            'labels'        => 'tickets',
            'description'   => 'Count of tickets created by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() as \'stat_value\', \'Tickets created\' as \'stat_description\' 
FROM tickets WHERE tickets.date_created = ${date}',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'chats-created-x-date' => [
            'title'         => 'Count of chats created ${date}',
            'labels'        => 'tickets',
            'description'   => 'Count of chats created by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() as \'stat_value\', \'Chats created\' as \'stat_description\' 
FROM chat_conversations WHERE chat_conversations.date_created = ${date}',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'avg-response-time-x-date' => [
            'title'         => 'Average response time of tickets created ${date}',
            'labels'        => 'tickets',
            'description'   => 'Average response time of tickets created by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT FORMAT(AVG(tickets.total_to_first_reply), \'number\') as \'stat_value\', \'Time to first reply\' as \'stat_description\' 
FROM tickets WHERE tickets.date_created = ${date} AND tickets.date_last_agent_reply <> NULL',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'satisfaction-x-date' => [
            'title'         => 'Percent of positive ratings ${date}',
            'labels'        => 'tickets',
            'description'   => 'Percent of positive ratings today by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            // all today created positive feedback / all today created feedback * 100 gives you today positive %
            'query' => 'SELECT CONCAT(FORMAT((
    (SELECT COUNT() FROM ticket_feedback WHERE ticket_feedback.rating = 1 AND ticket_feedback.date_created = ${date})
    / 
    COUNT()) * 100, \'number\'),
    \'%\'
) AS \'stat_value\',
\'Positive ratings\' as \'stat_description\'
FROM ticket_feedback
WHERE ticket_feedback.date_created = ${date}',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'replies-created-x-date' => [
            'title'         => 'Replies created ${date}',
            'labels'        => 'tickets',
            'description'   => 'Count of replies sent by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() AS \'stat_value\', \'Replies created\' as \'stat_description\'
FROM tickets_messages
WHERE tickets_messages.date_created = ${date}
  AND tickets_messages.is_agent_note = 0
  AND tickets_messages.person.is_agent = 1',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'tickets-resolved-x-date' => [
            'title'         => 'Count of tickets resolved ${date}',
            'labels'        => 'tickets',
            'description'   => 'Count of tickets resolved by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() AS \'stat_value\', \'Tickets resolved\' as \'stat_description\'
FROM tickets
WHERE tickets.status = \'resolved\'
  AND tickets.date_resolved = ${date}',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'number-of-replies-created-x-date-grouped-by-agent' => [
            'title'         => 'Agents with the number of replies ${date}',
            'labels'        => 'agents,tickets',
            'description'   => 'Agents with the number of replies by date',
            'display_types' => 'table',
            'display_order' => 40,
            'query'         => 'SELECT tickets_messages.person AS \'Agent\', COUNT() AS \'Replies\'
FROM tickets_messages
WHERE tickets_messages.date_created = ${date}
  AND tickets_messages.person.is_agent = 1
GROUP BY tickets_messages.person
ORDER BY COUNT() DESC
',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'tickets-opened-within-x-date-grouped-by-hour' => [
            'title'         => 'Tickets opened ${date}',
            'labels'        => 'tickets',
            'description'   => '',
            'display_types' => 'simple_bars',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() AS \'Tickets\', HOUR(tickets.date_created) as \'Created Hour\' 
            FROM  tickets 
            WHERE tickets.date_created = ${date}
            GROUP BY HOUR(tickets.date_created)
            ORDER BY HOUR(tickets.date_created)',
            'variables' => '[{"name":"date","type":"dates","default":"past_24_hours"}]',
        ],
        'daily-activity' => [
            'title'         => 'Daily activity ${date}',
            'labels'        => 'agents,tickets',
            'description'   => '',
            'display_types' => 'simple_bars',
            'display_order' => 40,
//            'query'         => 'SELECT SUM(count_open), SUM(count_resolve), hr
//FROM (
//	(SELECT COUNT() AS count_open, 0 AS count_resolve, HOUR(tickets.date_created) AS hr FROM tickets WHERE tickets.date_created = ${date} GROUP BY HOUR(tickets.date_created))
//	UNION
//	(SELECT 0 AS count_open, COUNT()*-1 AS count_resolve, HOUR(tickets.date_resolved) AS hr FROM tickets WHERE tickets.date_resolved = ${date} GROUP BY HOUR(tickets.date_resolved))
//) AS dat
//GROUP BY hr',
            'query' => 'SELECT COUNT() AS \'Replies\', HOUR(tickets_messages.date_created) as \'Reply Hour\' 
            FROM  tickets_messages 
            WHERE tickets_messages.date_created = ${date}
            GROUP BY HOUR(tickets_messages.date_created)
            ORDER BY HOUR(tickets_messages.date_created)',
            'variables' => '[{"name":"date","type":"dates","default":"yesterday"}]',
        ],
        'incomplete-sla' => [
            'title'         => 'SLA status of non completed SLAs',
            'labels'        => 'sla',
            'description'   => '',
            'display_types' => 'pie',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() AS \'count\', ticket_slas.sla_status
FROM ticket_slas
WHERE ticket_slas.is_completed = 0
GROUP BY ticket_slas.sla_status',
            'variables' => '[]',
        ],
        'tickets-replied-x-date-grouped-by-first-reply' => [
            'title'         => 'Count of tickets replied ${date} grouped by time to first reply',
            'labels'        => 'tickets',
            'description'   => '',
            'display_types' => 'pie',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() AS \'count\', DATE_OFFSET_GROUP(tickets.total_to_first_reply) AS TimeToReply
FROM tickets
WHERE tickets.date_created = ${date} AND tickets.date_first_agent_reply <> NULL
GROUP BY DATE_OFFSET_GROUP(tickets.total_to_first_reply)',
            'variables' => '[]',
        ],
        'tickets-by-channel-created-x-date' => [
            'title'         => 'Tickets created ${date} grouped by channel',
            'labels'        => 'tickets',
            'description'   => '',
            'display_types' => 'pie',
            'display_order' => 40,
//            'query'         => ' SELECT tickets.date_created FROM (
//(SELECT \'Portal\' AS channel, COUNT() FROM tickets WHERE tickets.date_created = ${date} AND tickets.creation_system IN (\'web.person\', \'web.person.portal\'))
//UNION
//(SELECT \'Website Widget\' AS channel, COUNT() FROM tickets WHERE tickets.date_created = ${date} AND tickets.creation_system  (\'web.person.widget\'))
//UNION
//(SELECT \'Embedded Form\' AS channel, COUNT() FROM tickets WHERE tickets.date_created = ${date} AND tickets.creation_system  (\'web.person.embed\'))
//UNION
//(SELECT \'Email\' AS channel, COUNT() FROM tickets WHERE tickets.date_created = ${date} AND tickets.creation_system IN (\'gateway.person\', \'gateway.agent\'))
//UNION
//(SELECT \'API\' AS channel, COUNT() FROM tickets WHERE tickets.date_created = ${date} AND tickets.creation_system IN (\'web.api\', \'web.api.person\', \'web.api.agent\'))
//) as dat
//',
            'query'     => 'SELECT COUNT(), tickets.creation_system FROM tickets WHERE tickets.date_created = ${date} GROUP BY tickets.creation_system',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',

        ],
        'kb-views-x-date' => [
            'title'         => 'Knowledgebase views by ${date}',
            'labels'        => 'kb',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 40,
            'query'         => 'SELECT JSON_EXTRACT(hit_record.meta, \'$.pageTitle\') AS \'Title\', COUNT() AS \'count\'
FROM hit_record
WHERE hit_record.date_created = ${date}
  AND hit_record.page_type = \'deskpro.kb_view\'
GROUP BY hit_record.page_id
ORDER BY COUNT()',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'kb-searches-x-date' => [
            'title'         => 'KB searches made ${date} ordered by search term',
            'labels'        => 'kb',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() AS \'count\', searchlog.query
FROM searchlog
WHERE searchlog.date_created = ${date}
GROUP BY searchlog.query
ORDER BY searchlog.query DESC
',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'top-snippets-x-date' => [
            'title'         => 'Count of snippets uses ${date}',
            'labels'        => 'agents',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 40,
            'query'         => 'SELECT COUNT() AS \'count\', snippet_use_log.snippet.title
FROM snippet_use_log
WHERE snippet_use_log.date_created = ${date}
GROUP BY snippet_use_log.snippet.id',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'article-views-date-x-grouped-date' => [
            'title'         => 'Number of article views ${date} grouped by date',
            'labels'        => 'kb',
            'description'   => '',
            'display_types' => 'table,simple_lines',
            'display_order' => 40,
            'query'         => 'DISPLAY TABLE, LINE
SELECT COUNT() AS \'Views\'
FROM articles
WHERE articles.views.date_created = ${date}
GROUP BY ALIAS(DATE(articles.views.date_created), \'Date\')',
            'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'average-chat-length-chats-created-group-x' => [
                'title'         => 'Average chat length for chats created ${date} grouped by ${chat}',
                'labels'        => 'chat',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 30,
                'query'         => 'DISPLAY TABLE, BAR
SELECT AVG(chat_conversations.total_to_ended) / 60 AS \'Average Length (Minutes)\'
FROM chat_conversations
WHERE chat_conversations.date_created = ${date} AND chat_conversations.is_agent = 0 AND chat_conversations.status = \'ended\' AND chat_conversations.total_to_ended > 0
GROUP BY ${chat}',
                'variables' => '[{"name":"chat","type":"fields","field_type":"chats","table":"chat_conversations","default":"agent"},{"name":"date","type":"dates"}]',
            ],
        'average-time-first-re-tickets-created-date-group-x' => [
                'title'         => 'Average time to first response in tickets created ${date} grouped by ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 120,
                'query'         => 'DISPLAY TABLE, BAR
SELECT AVG(UNIX_TIMESTAMP(tickets.date_first_agent_reply) - UNIX_TIMESTAMP(tickets.date_created)) / (60 * 60) AS \'Average Time (Hours)\', COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = ${date} AND tickets.date_first_agent_reply <> NULL
GROUP BY ${ticket}',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets"},{"name":"date","type":"dates"}]',
            ],
        'average-time-resolve-tickets-date-group-by-x' => [
                'title'         => 'Average time to resolve tickets ${date} grouped by ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 100,
                'query'         => 'DISPLAY TABLE, BAR
SELECT AVG(UNIX_TIMESTAMP(tickets.date_resolved) - UNIX_TIMESTAMP(tickets.date_created)) / (60 * 60) AS \'Average Time (Hours)\', COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = ${date} AND tickets.date_resolved <> NULL
GROUP BY ${ticket}',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets"},{"name":"date","type":"dates"}]',
            ],
        'average-total-wait-tickets-resolve-date-group-by-x' => [
                'title'         => 'Average total waiting time for tickets resolved ${date} grouped by ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 110,
                'query'         => 'DISPLAY TABLE, BAR
SELECT AVG(tickets.total_user_waiting) / (60 * 60) AS \'Total Waiting Time (Hours)\', COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = ${date}
GROUP BY ${ticket}',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets"},{"name":"date","type":"dates"}]',
            ],
        'feedback-views-date-x-grouped-date' => [
                'title'         => 'Number of feedback views ${date} grouped by date',
                'labels'        => 'feedback',
                'description'   => '',
                'display_types' => 'table,simple_lines',
                'display_order' => 40,
                'query'         => 'DISPLAY TABLE, LINE
SELECT COUNT() AS \'Views\'
FROM feedback
WHERE feedback.views.date_created = ${date}
GROUP BY ALIAS(DATE(feedback.views.date_created), \'Date\')',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'most-active-tickets-status-created-date' => [
                'title'         => 'Most active tickets ${status} created ${date}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 210,
                'query'         => '
SELECT tickets_messages.ticket.subject, COUNT() AS \'Messages\', tickets_messages.ticket.person, tickets_messages.ticket.department, tickets_messages.ticket.date_created, tickets_messages.ticket.agent
FROM tickets_messages
WHERE ${status} AND tickets_messages.ticket.date_created = ${date}
GROUP BY tickets_messages.ticket.id
ORDER BY COUNT() DESC
LIMIT 100',
                'variables' => '[{"name":"status","type":"statuses","field_type":"tickets","table":"tickets_messages.ticket","default":"awaiting_agent"},{"name":"date","type":"dates"}]',
            ],
        'most-popular-email-domains-ticket-usage' => [
                'title'         => 'Most popular email domains by ticket usage',
                'labels'        => 'person',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 0,
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Uses\'
FROM tickets
GROUP BY tickets.person_email.email_domain
ORDER BY COUNT() DESC
LIMIT 100',
                'variables' => '[]',
            ],
        'most-popular-people-email-domains' => [
                'title'         => 'Most popular primary email domains',
                'labels'        => 'person',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 0,
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total People\'
FROM people
GROUP BY people.primary_email.email_domain
ORDER BY COUNT() DESC
LIMIT 100',
                'variables' => '[]',
            ],
        'number-article-com-created-date-group-by-article' => [
                'title'         => 'Number of article comments created ${date} grouped by article ${article}',
                'labels'        => 'kb',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 30,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Comments Created\'
FROM article_comments
WHERE article_comments.date_created = ${date}
GROUP BY ${article}',
                'variables' => '[{"name":"article","type":"fields","field_type":"articles","table":"article_comments.article","default":"person"},{"name":"date","type":"dates"}]',
            ],
        'number-article-comments-created-date-group-by-x' => [
                'title'         => 'Number of article comments created ${date} grouped by ${article_comment}',
                'labels'        => 'kb',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 20,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Comments Created\'
FROM article_comments
WHERE article_comments.date_created = ${date}
GROUP BY ${article_comment}',
                'variables' => '[{"name":"article_comment","type":"fields","field_type":"article_comments","table":"article_comments","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-articles-created-date-group-by-x' => [
                'title'         => 'Number of articles created ${date} grouped by ${article}',
                'labels'        => 'kb',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 10,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Entries Created\'
FROM articles
WHERE articles.date_created = ${date}
GROUP BY ${article}',
                'variables' => '[{"name":"article","type":"fields","field_type":"articles","table":"articles","default":"person"},{"name":"date","type":"dates"}]',
            ],
        'number-chats-created-date-grouped-by-x' => [
                'title'         => 'Number of chats created ${date} grouped by ${chat}',
                'labels'        => 'chat',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 10,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Chats Created\'
FROM chat_conversations
WHERE chat_conversations.date_created = ${date} AND chat_conversations.is_agent = 0
GROUP BY ${chat}',
                'variables' => '[{"name":"chat","type":"fields","field_type":"chats","table":"chat_conversations","default":"agent"},{"name":"date","type":"dates"}]',
            ],
        'number-chats-missed-date-grouped-by-x' => [
                'title'         => 'Number of chats missed ${date} grouped by ${chat}',
                'labels'        => 'chat',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 20,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Chats Created\'
FROM chat_conversations
WHERE chat_conversations.date_created = ${date} AND chat_conversations.is_agent = 0 AND chat_conversations.agent_id = NULL
GROUP BY ${chat}',
                'variables' => '[{"name":"chat","type":"fields","field_type":"chats","table":"chat_conversations","default":"department"},{"name":"date","type":"dates"}]',
            ],
        'number-feedback-com-created-date-group-by-feedback' => [
                'title'         => 'Number of feedback comments created ${date} grouped by feedback ${feedback}',
                'labels'        => 'feedback',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 30,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Comments Created\'
FROM feedback_comments
WHERE feedback_comments.date_created = ${date}
GROUP BY ${feedback}',
                'variables' => '[{"name":"feedback","type":"fields","field_type":"feedback","table":"feedback_comments.feedback","default":"type"},{"name":"date","type":"dates"}]',
            ],
        'number-feedback-comments-created-date-group-by-x' => [
                'title'         => 'Number of feedback comments created ${date} grouped by ${feedback_comment}',
                'labels'        => 'feedback',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 20,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Comments Created\'
FROM feedback_comments
WHERE feedback_comments.date_created = ${date}
GROUP BY ${feedback_comment}',
                'variables' => '[{"name":"feedback_comment","type":"fields","field_type":"feedback_comments","table":"feedback_comments","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-feedback-created-date-group-by-x' => [
                'title'         => 'Number of feedback entries created ${date} grouped by ${feedback}',
                'labels'        => 'feedback',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 10,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Entries Created\'
FROM feedback
WHERE feedback.date_created = ${date}
GROUP BY ${feedback}',
                'variables' => '[{"name":"feedback","type":"fields","field_type":"feedback","table":"feedback","default":"type"},{"name":"date","type":"dates"}]',
            ],
        'number-feedback-votes-submitted-date-x-group-y' => [
                'title'         => 'Number of feedback votes submitted ${date} grouped by ${feedback}',
                'labels'        => 'feedback',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 60,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Ratings\'
FROM feedback
WHERE feedback.ratings.date_created = ${date}
GROUP BY ${feedback}',
                'variables' => '[{"name":"feedback","type":"fields","field_type":"feedback","table":"feedback","default":"type"},{"name":"date","type":"dates"}]',
            ],
        'number-ticket-messages-written-agent-day' => [
                'title'         => 'Number of ticket messages written ${date} per agent per [day]',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 140,
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = ${date}
SPLIT BY tickets_messages.person
GROUP BY CONCAT(YEAR(tickets_messages.date_created), \'-\', LPAD(MONTHNAME(tickets_messages.date_created), 2, \'0\'), \'-\', LPAD(DAYOFMONTH(tickets_messages.date_created), 2, \'0\')) AS \'Period\'
ORDER BY tickets_messages.date_created',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'number-ticket-messages-written-agent-month' => [
                'title'         => 'Number of ticket messages written ${date} per agent per [month]',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 140,
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = ${date}
SPLIT BY tickets_messages.person
GROUP BY CONCAT(YEAR(tickets_messages.date_created), \'-\', LPAD(MONTHNAME(tickets_messages.date_created), 2, \'0\')) AS \'Period\'
ORDER BY tickets_messages.date_created',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'number-ticket-messages-written-agent-week' => [
                'title'         => 'Number of ticket messages written ${date} per agent per [week]',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 140,
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = ${date} 
SPLIT BY tickets_messages.person
GROUP BY CONCAT(\'Week \', WEEKOFYEAR(tickets_messages.date_created), \', \', YEAR(tickets_messages.date_created)) AS \'Period\'
ORDER BY tickets_messages.date_created',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'number-ticket-messages-written-agent-year' => [
                'title'         => 'Number of ticket messages written ${date} per agent per [year]',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 140,
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = ${date}
SPLIT BY tickets_messages.person
GROUP BY YEAR(tickets_messages.date_created) AS \'Period\'
ORDER BY YEAR(tickets_messages.date_created) DESC',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'number-tickets-created-date-grouped-by-date-and-x' => [
                'title'         => 'Number of tickets created ${date} grouped by date created & ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_area',
                'display_order' => 15,
                'query'         => 'DISPLAY TABLE, AREA
SELECT COUNT() AS \'Tickets Created\'
FROM tickets
WHERE tickets.date_created = ${date}
GROUP BY ALIAS(DATE(tickets.date_created), \'Date Created\'), ${ticket}',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-tickets-created-date-grouped-by-x-y' => [
                'title'         => 'Number of tickets created ${date} grouped by ${ticket} & ${ticket_2}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 10,
                'query'         => 'SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = ${date}
GROUP BY MATRIX(${ticket}, ${ticket_2})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"department"},{"name":"ticket_2","type":"fields","field_type":"tickets","table":"tickets","default":"agent"},{"name":"date","type":"dates"}]',
            ],
        'tickets-created-x-date-grouped-by-department' => [
            'title'         => 'Number of tickets created ${date} grouped by ${ticket}',
            'labels'        => 'tickets',
            'description'   => '',
            'display_types' => 'simple_bars,pie,simple_area,simple_lines',
            'display_order' => 10,
            'query'         => 'SELECT COUNT() AS \'Total Tickets\', tickets.department.title AS \'Department title\'
FROM tickets
WHERE tickets.date_created = ${date}
GROUP BY tickets.department.title',
            'variables' => '[{"name":"date","type":"dates"}]',
        ],
        'number-tickets-created-date-grouped-first-agent-x' => [
                'title'         => 'Number of tickets created ${date} grouped by first agent response time & ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 90,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = ${date}
GROUP BY MATRIX(ALIAS(DATE_OFFSET_GROUP(NOW(), tickets.date_first_agent_reply), \'Time Waiting\'), ${ticket})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-tickets-resolved-date-grouped-time-res-x' => [
                'title'         => 'Number of tickets resolved ${date} grouped by time to resolution & ${ticket}',
                'labels'        => 'tickets,resolved',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 80,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = ${date}
GROUP BY MATRIX(ALIAS(DATE_OFFSET_GROUP(tickets.date_resolved, tickets.date_created), \'Time To Resolve\'), ${ticket})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-tickets-resolved-date-grouped-total-wait-x' => [
                'title'         => 'Number of tickets resolved ${date} grouped by total waiting time & ${ticket}',
                'labels'        => 'tickets,resolved',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 70,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = ${date}
GROUP BY MATRIX(ALIAS(DATE_OFFSET_GROUP(tickets.total_user_waiting), \'Time Waiting\'), ${ticket})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-tickets-resolved-date-grouped-x-y' => [
                'title'         => 'Number of tickets resolved ${date} grouped by ${ticket} & ${ticket_2}',
                'labels'        => 'tickets,resolved',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 30,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_resolved = ${date} AND tickets.status IN (\'resolved\', \'archived\')
GROUP BY MATRIX(${ticket}, ${ticket_2})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"department"},{"name":"ticket_2","type":"fields","field_type":"tickets","table":"tickets","default":"agent"},{"name":"date","type":"dates"}]',
            ],
        'number-tickets-status-grouped-by-x-y' => [
                'title'         => 'Number of tickets ${status} grouped by ${ticket} & ${ticket_2}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 20,
                'query'         => '
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE ${status}
GROUP BY MATRIX(${ticket}, ${ticket_2})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"department"},{"name":"ticket_2","type":"fields","field_type":"tickets","table":"tickets","default":"agent"},{"name":"status","type":"statuses","field_type":"tickets","table":"tickets","default":"awaiting_agent"}]',
            ],
        'number-tickets-wait-agent-grouped-time-wait-ag-x' => [
                'title'         => 'Number of tickets awaiting agent grouped by time awaiting agent and ${ticket}',
                'labels'        => 'tickets,agents',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 50,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status = \'awaiting_agent\'
GROUP BY MATRIX(ALIAS(DATE_OFFSET_GROUP(NOW(), tickets.date_user_waiting), \'Time Waiting\'), ${ticket})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"}]',
            ],
        'number-tickets-wait-agent-grouped-total-wait-x' => [
                'title'         => 'Number of tickets awaiting agent grouped by total waiting time and ${ticket}',
                'labels'        => 'tickets,agents',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 60,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status = \'awaiting_agent\'
GROUP BY MATRIX(ALIAS(DATE_OFFSET_GROUP(tickets.total_user_waiting), \'Time Waiting\'), ${ticket})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"}]',
            ],
        'number-views-per-article-date-x' => [
                'title'         => 'Number of views per article ${date}',
                'labels'        => 'kb',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 50,
                'query'         => 'DISPLAY TABLE
SELECT articles.title, COUNT() AS \'Views\'
FROM articles
WHERE articles.views.date_created = ${date}
GROUP BY articles.id
ORDER BY COUNT() DESC',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'number-views-per-feedback-date-x' => [
                'title'         => 'Number of views per feedback entry ${date}',
                'labels'        => 'feedback,agents',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 50,
                'query'         => 'DISPLAY TABLE
SELECT feedback.title, COUNT() AS \'Views\'
FROM feedback
WHERE feedback.views.date_created = ${date}
GROUP BY feedback.id
ORDER BY COUNT() DESC',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'organizations-longest-total--wait' => [
                'title'         => '[Organizations] with longest total waiting time',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 200,
                'query'         => 'DISPLAY TABLE
SELECT SUM(tickets.total_user_waiting) / (60 * 60) AS \'Total Wait (Hours)\', COUNT() AS \'Total Tickets\', AVG(tickets.total_user_waiting) / (60 * 60) AS \'Average Wait (Hours)\'
FROM tickets
GROUP BY tickets.organization
ORDER BY SUM(tickets.total_user_waiting) DESC
LIMIT 100',
                'variables' => '[]',
            ],
        'organizations-longest-total-first-reply-wait' => [
                'title'         => '[Organizations] with longest total first reply waiting time',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 190,
                'query'         => 'DISPLAY TABLE
SELECT SUM(tickets.total_to_first_reply) / (60 * 60) AS \'Total Wait (Hours)\', COUNT() AS \'Total Tickets\', AVG(tickets.total_to_first_reply) / (60 * 60) AS \'Average Wait (Hours)\'
FROM tickets
GROUP BY tickets.organization
ORDER BY SUM(tickets.total_to_first_reply) DESC
LIMIT 100',
                'variables' => '[]',
            ],
        'people-longest-total--wait' => [
                'title'         => '[People] with longest total waiting time',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 200,
                'query'         => 'DISPLAY TABLE
SELECT SUM(tickets.total_user_waiting) / (60 * 60) AS \'Total Wait (Hours)\', COUNT() AS \'Total Tickets\', AVG(tickets.total_user_waiting) / (60 * 60) AS \'Average Wait (Hours)\'
FROM tickets
GROUP BY tickets.person
ORDER BY SUM(tickets.total_user_waiting) DESC
LIMIT 100',
                'variables' => '[]',
            ],
        'people-longest-total-first-reply-wait' => [
                'title'         => '[People] with longest total first reply waiting time',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 190,
                'query'         => 'DISPLAY TABLE
SELECT SUM(tickets.total_to_first_reply) / (60 * 60) AS \'Total Wait (Hours)\', COUNT() AS \'Total Tickets\', AVG(tickets.total_to_first_reply) / (60 * 60) AS \'Average Wait (Hours)\'
FROM tickets
GROUP BY tickets.person
ORDER BY SUM(tickets.total_to_first_reply) DESC
LIMIT 100',
                'variables' => '[]',
            ],
        'percent-ticket-create-date-resolved-24hour-group-x' => [
                'title'         => 'Percentage of tickets created ${date} resolved within 24 hours, grouped by ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 131,
                'query'         => 'DISPLAY TABLE, BAR
SELECT PERCENT(tickets.date_resolved <> NULL AND UNIX_TIMESTAMP(tickets.date_resolved) - UNIX_TIMESTAMP(tickets.date_created) < 24 * 60 * 60) AS \'Percentage\'
FROM tickets
WHERE tickets.date_created = ${date}
GROUP BY ${ticket}',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets"},{"name":"date","type":"dates"}]',
            ],
        'percent-tickets-created-date-replied-hour-group-x' => [
                'title'         => 'Percentage of tickets created ${date} replied to within an hour, grouped by ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 130,
                'query'         => 'DISPLAY TABLE, BAR
SELECT PERCENT(tickets.total_to_first_reply < 3600) AS \'Percentage\'
FROM tickets
WHERE tickets.date_created = ${date} AND tickets.total_to_first_reply > 0
GROUP BY ${ticket}',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets"},{"name":"date","type":"dates"}]',
            ],
        'percent-tickets-created-date-res-1-agent-group-x' => [
                'title'         => 'Percentage of tickets created ${date} resolved by first response, grouped by ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 135,
                'query'         => 'DISPLAY TABLE, BAR
SELECT PERCENT(tickets.count_agent_replies = 1) AS \'Percentage\'
FROM tickets
WHERE tickets.date_created = ${date} AND tickets.date_resolved <> NULL AND tickets.count_agent_replies > 0
GROUP BY ${ticket}',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"agent"},{"name":"date","type":"dates"}]',
            ],
        'sla-date-groupby-x' => [
                'title'         => 'SLA Statuses for tickets created ${date} grouped by ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 96,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = ${date} AND tickets.ticket_slas.sla_status IN (\'ok\', \'warning\', \'fail\')
GROUP BY ${ticket}, tickets.ticket_slas.sla_status',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"agent"},{"name":"date","type":"dates"}]',
            ],
        'sla-status-date-splitby-x' => [
                'title'         => 'SLA Statuses for tickets created ${date} split by ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,pie',
                'display_order' => 95,
                'query'         => 'DISPLAY TABLE, PIE
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = ${date} AND tickets.ticket_slas.sla_status IN (\'ok\', \'warning\', \'fail\')
SPLIT BY ${ticket}
GROUP BY tickets.ticket_slas.sla_status',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"sla"},{"name":"date","type":"dates"}]',
            ],
        'tickets-awaiting-agent-split-by-field-ordered-by-x' => [
                'title'         => 'Tickets awaiting agent split by ${ticket} ordered by ${ticket_2}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 40,
                'query'         => '
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent
FROM tickets
WHERE tickets.status = \'awaiting_agent\'
SPLIT BY ${ticket}
ORDER BY ${ticket_2}
LIMIT 100',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets"},{"name":"ticket_2","type":"orders","field_type":"tickets","table":"tickets"}]',
            ],
        'tickets-created-date-grouped-labels' => [
                'title'         => 'Tickets created ${date} grouped by labels',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 170,
                'query'         => 'DISPLAY TABLE, BAR
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.labels.label <> NULL AND tickets.date_created = ${date}
GROUP BY tickets.labels',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'tickets-resolved-date-grouped-by-agent-resolving' => [
                'title'         => 'Tickets resolved ${date} grouped by agent resolving ticket',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 160,
                'query'         => 'DISPLAY TABLE
SELECT COUNT_DISTINCT(tickets_logs.ticket_id) AS \'Tickets Resolved\'
FROM tickets_logs
WHERE tickets_logs.action_type = \'changed_status\' AND tickets_logs.id_after = 200 AND tickets_logs.ticket.status IN (\'resolved\', \'archived\') AND tickets_logs.date_created = ${date}
GROUP BY ALIAS(IF(tickets_logs.person.is_agent, tickets_logs.person, \'Non-Agent\'), \'Person\')
ORDER BY @\'Tickets Resolved\' DESC
LIMIT 100',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'tickets-split-by-labels' => [
                'title'         => 'Tickets split by labels',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 180,
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent
FROM tickets
WHERE tickets.labels.label <> NULL
SPLIT BY tickets.labels
ORDER BY tickets.date_created DESC
LIMIT 100',
                'variables' => '[]',
            ],
        'tickets-unresolved-split-field-ordered-x' => [
                'title'         => 'Tickets unresolved split by ${ticket} ordered by ${ticket_2}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 41,
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'awaiting_user\')
SPLIT BY ${ticket}
ORDER BY ${ticket_2}
LIMIT 100',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets"},{"name":"ticket_2","type":"orders","field_type":"tickets","table":"tickets"}]',
            ],
        'total-tickets-unresolved-after-week' => [
                'title'         => 'Total tickets unresolved after a week',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 220,
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total\', COUNT(tickets.status = \'awaiting_agent\') AS \'Total Awaiting Agent\', COUNT(tickets.status = \'awaiting_user\') AS \'Total Awaiting User\'
FROM tickets
WHERE tickets.status IN (\'awaiting_user\', \'awaiting_agent\') AND tickets.date_created < %PAST_7_DAYS%',
                'variables' => '[]',
            ],
        'unresolved-high-priority-tickets' => [
                'title'         => 'Unresolved [high priority] tickets',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 230,
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent, tickets.priority
FROM tickets
WHERE tickets.priority.priority = 1 AND tickets.status IN (\'awaiting_user\', \'awaiting_agent\')
ORDER BY tickets.date_created
LIMIT 100',
                'variables' => '[]',
            ],
        'unresolved-tickets-10-more-agent-replies' => [
                'title'         => 'Unresolved tickets with 10 or more agent replies',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 240,
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent, tickets.count_agent_replies AS \'Agent Replies\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'awaiting_user\') AND tickets.count_agent_replies >= 10
ORDER BY tickets.date_created
LIMIT 100',
                'variables' => '[]',
            ],
        'unresolved-urgent-tickets' => [
                'title'         => 'Unresolved [urgent] tickets',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 230,
                'query'         => 'DISPLAY TABLE
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent, tickets.urgency
FROM tickets
WHERE tickets.urgency > 7 AND tickets.status IN (\'awaiting_user\', \'awaiting_agent\')
ORDER BY tickets.date_created
LIMIT 100',
                'variables' => '[]',
            ],
        'users-most-unresolved-tickets' => [
                'title'         => 'Users with most unresolved tickets',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 150,
                'query'         => 'DISPLAY TABLE
SELECT COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'awaiting_user\', \'awaiting_agent\')
GROUP BY tickets.person
ORDER BY COUNT() DESC
LIMIT 100',
                'variables' => '[]',
            ],
    ];

    /**
     * Called during a fresh install.
     */
    public function runInstall()
    {
        /* @var ReportWidgetRepository $repo */
        $ids      = array_keys($this->data);
        $em       = $this->getEm();
        $schedule = $em->createQuery('
            SELECT r
            FROM DeskPRO:ReportWidget r
            WHERE r.unique_key IS NOT NULL
        ')->execute();
        $updated = $existing = [];

        foreach ($schedule as $report) {
            /* @var ReportWidget $report */
            $existing[] = $report->getUniqueKey();
            if (array_key_exists($report->getUniqueKey(), $this->data)) {
                $updated[] = $report->getUniqueKey();
                $data      = $this->data[$report->getUniqueKey()];
                $report
                    ->setTitle($data['title'])
                    ->setQuery($data['query'])
                    ->setDisplayOrder($data['display_order'])
                    ->setDescription($data['description'])
                    ->setDisplayTypes(explode(',', $data['display_types']))
                    ->setLabels((array) $data['labels'])
                    ->setIsCustom(false)
                    ->setVariables(json_decode($data['variables'], true));
                $em->persist($report);
            } else {
                $em->remove($report);
            }
        }

        $scheduledToInsert = array_diff($ids, $updated);

        foreach ($scheduledToInsert as $insert) {
            $report = new ReportWidget();
            $data   = $this->data[$insert];
            $report
                ->setUniqueKey($insert)
                ->setTitle($data['title'])
                ->setQuery($data['query'])
                ->setDisplayOrder($data['display_order'])
                ->setDescription($data['description'])
                ->setDisplayTypes(explode(',', $data['display_types']))
                ->setLabels((array) $data['labels'])
                ->setIsCustom(false)
                ->setVariables(json_decode($data['variables'], true));
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
