<?php

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
            'query'         => 'SELECT DPQL_COUNT() as \'stat_value\', IF(DPQL_COUNT() = 1, \'ticket waiting\', \'tickets waiting\') as \'stat_description\'
            FROM tickets WHERE tickets.status IN (\'awaiting_agent\', \'pending\')',
            'variables' => '[]',
        ],
        'agents-online' => [
            'title'         => 'Count of agents online right now',
            'labels'        => 'agents',
            'description'   => 'Agents are online',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_COUNT_DISTINCT(sessions.person.id) as \'stat_value\',  IF(DPQL_COUNT_DISTINCT(sessions.person.id) = 1, \'online agent\', \'online agents\') as \'stat_description\'
FROM sessions WHERE sessions.person.is_agent = 1 AND sessions.date_last > DPQL_NOW() - INTERVAL 6 MINUTE',
            'variables' => '[]',
        ],
        'tickets-created-x-date' => [
            'title'         => 'Count of tickets created ${date}',
            'labels'        => 'tickets',
            'description'   => 'Count of tickets created by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_COUNT() as \'stat_value\', IF(DPQL_COUNT() = 1, \'ticket created\', \'tickets created\') as \'stat_description\'
FROM tickets WHERE tickets.date_created = ${date}',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'chats-created-x-date' => [
            'title'         => 'Count of chats created ${date}',
            'labels'        => 'chat',
            'description'   => 'Count of chats created by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_COUNT() as \'stat_value\', IF(DPQL_COUNT() = 1, \'chat created\', \'chats created\') as \'stat_description\'
FROM chat_conversations WHERE chat_conversations.date_created = ${date}',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'chats-feedback-grouped-by-agent-x-date' => [
            'title'         => 'Chat feedback grouped by agent created ${date}',
            'labels'        => 'chat',
            'description'   => 'Chat feedback grouped by agent created ${date}',
            'display_types' => 'table',
            'display_order' => 40,
            'query'         => '
            SELECT
                SUM(IF(chat_conversations.rating_overall > 0,1,0)) AS Postive,
                SUM(IF(chat_conversations.rating_overall <= 0,1,0)) AS Negative
            FROM chat_conversations
            WHERE chat_conversations.rating_overall <> NULL
              AND chat_conversations.agent <> NULL
              AND chat_conversations.date_created = ${date}
            GROUP BY chat_conversations.agent
            ',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'avg-response-time-x-date' => [
            'title'         => 'Average response time of tickets created ${date}',
            'labels'        => 'tickets',
            'description'   => 'Average response time of tickets created by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_FORMAT(AVG(tickets.total_to_first_reply) / 60, \'number\', 0) as \'stat_value\', IF(DPQL_COUNT() = 1, \'minute to reply\', \'minutes to reply\') as \'stat_description\'
FROM tickets WHERE tickets.date_created = ${date} AND tickets.date_first_agent_reply <> NULL',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'satisfaction-x-date' => [
            'title'         => 'Percent of positive ratings ${date}',
            'labels'        => 'tickets',
            'description'   => 'Percent of positive ratings today by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            // all today created positive feedback / all today created feedback * 100 gives you today positive %
            'query' => '
            SELECT
    DPQL_FORMAT(
	(
    	 (SELECT DPQL_COUNT() FROM ticket_feedback WHERE ticket_feedback.rating = 1 AND ticket_feedback.date_created = ${date})
    	  /
    	 (SELECT DPQL_COUNT() FROM ticket_feedback WHERE ticket_feedback.date_created = ${date})
    	),
    \'percent\', 0)
    AS \'stat_value\',
\'satisfied users\' as \'stat_description\'
FROM ticket_feedback
WHERE ticket_feedback.date_created = ${date}
            ',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'replies-created-x-date' => [
            'title'         => 'Replies created ${date}',
            'labels'        => 'tickets',
            'description'   => 'Count of replies sent by date',
            'display_types' => 'simple_stat',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_COUNT() AS \'stat_value\', IF(DPQL_COUNT() = 1, \'reply sent\', \'replies sent\') as \'stat_description\'
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
            'query'         => 'SELECT DPQL_COUNT() AS \'stat_value\', IF(DPQL_COUNT() = 1, \'ticket resolved\', \'tickets resolved\') as \'stat_description\'
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
            'query'         => 'SELECT tickets_messages.person AS \'Agent\', DPQL_COUNT() AS \'Replies\'
FROM tickets_messages
WHERE tickets_messages.date_created = ${date}
  AND tickets_messages.is_agent_note = 0
  AND tickets_messages.person.is_agent = 1
GROUP BY tickets_messages.person
ORDER BY @\'Replies\' DESC
',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'tickets-opened-within-x-date-grouped-by-hour' => [
            'title'         => 'Tickets opened ${date}',
            'labels'        => 'tickets',
            'description'   => '',
            'display_types' => 'simple_bars',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_COUNT() AS \'Tickets\', DPQL_HOUR(tickets.date_created) as \'Created Hour\'
            FROM  tickets
            WHERE tickets.date_created = ${date}
            GROUP BY @\'Created Hour\'
            ORDER BY @\'Created Hour\'',
            'variables' => '[{"name":"date","type":"dates","default":"past_24_hours"}]',
        ],
        'agent-replies-by-hour' => [
            'title'         => 'Agent Replies by Hour',
            'labels'        => 'agents,tickets',
            'description'   => '',
            'display_types' => 'simple_bars',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_COUNT() AS \'Replies\'
            FROM  tickets_messages
            WHERE
                tickets_messages.date_created = %PAST_24_HOURS%
                AND tickets_messages.is_agent_note = 0
                AND tickets_messages.person.is_agent = 1
            GROUP BY DPQL_HOUR(tickets_messages.date_created, 0, 23) AS \'Hour\'',
            'variables' => '[]',
        ],
        'incomplete-sla' => [
            'title'         => 'SLA status of non completed SLAs',
            'labels'        => 'sla',
            'description'   => '',
            'display_types' => 'pie',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_COUNT() AS \'count\', ticket_slas.sla_status
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
            'query'         => 'SELECT DPQL_COUNT() AS \'count\', DPQL_DATE_OFFSET_GROUP(tickets.total_to_first_reply) AS TimeToReply
FROM tickets
WHERE tickets.date_created = ${date} AND tickets.date_first_agent_reply <> NULL
GROUP BY DPQL_DATE_OFFSET_GROUP(tickets.total_to_first_reply)',
            'variables' => '[]',
        ],
        'tickets-by-channel-created-x-date' => [
            'title'         => 'Tickets created ${date} grouped by channel',
            'labels'        => 'tickets',
            'description'   => '',
            'display_types' => 'pie',
            'display_order' => 40,
//            'query'         => ' SELECT tickets.date_created FROM (
//(SELECT \'Portal\' AS channel, DPQL_COUNT() FROM tickets WHERE tickets.date_created = ${date} AND tickets.creation_system IN (\'web.person\', \'web.person.portal\'))
//UNION
//(SELECT \'Website Widget\' AS channel, DPQL_COUNT() FROM tickets WHERE tickets.date_created = ${date} AND tickets.creation_system  (\'web.person.widget\'))
//UNION
//(SELECT \'Embedded Form\' AS channel, DPQL_COUNT() FROM tickets WHERE tickets.date_created = ${date} AND tickets.creation_system  (\'web.person.embed\'))
//UNION
//(SELECT \'Email\' AS channel, DPQL_COUNT() FROM tickets WHERE tickets.date_created = ${date} AND tickets.creation_system IN (\'gateway.person\', \'gateway.agent\'))
//UNION
//(SELECT \'API\' AS channel, DPQL_COUNT() FROM tickets WHERE tickets.date_created = ${date} AND tickets.creation_system IN (\'web.api\', \'web.api.person\', \'web.api.agent\'))
//) as dat
//',
            'query'     => 'SELECT DPQL_COUNT(), tickets.creation_system FROM tickets WHERE tickets.date_created = ${date} GROUP BY tickets.creation_system',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',

        ],
        'kb-views-x-date' => [
            'title'         => 'Knowledgebase views by ${date}',
            'labels'        => 'kb',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_JSON_EXTRACT(hit_record.meta, \'$.pageTitle\') AS \'Title\', DPQL_COUNT() AS \'count\'
FROM hit_record
WHERE hit_record.date_created = ${date}
  AND hit_record.page_type = \'deskpro.kb_view\'
GROUP BY hit_record.page_id
ORDER BY DPQL_COUNT() DESC',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'kb-searches-x-date' => [
            'title'         => 'KB searches made ${date} ordered by search term',
            'labels'        => 'kb',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_COUNT() AS \'count\', searchlog.query
FROM searchlog
WHERE searchlog.date_created = ${date}
GROUP BY searchlog.query
ORDER BY DPQL_COUNT() DESC
',
            'variables' => '[{"name":"date","type":"dates","default":"today"}]',
        ],
        'top-snippets-x-date' => [
            'title'         => 'Count of snippets uses ${date}',
            'labels'        => 'agents',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 40,
            'query'         => 'SELECT DPQL_COUNT() AS \'count\', snippet_use_log.snippet.title
FROM snippet_use_log
WHERE snippet_use_log.date_created = ${date}
GROUP BY snippet_use_log.snippet.id
ORDER BY DPQL_COUNT() DESC',
            'variables' => '[{"name":"date","type":"dates","default":"last_month"}]',
        ],
        'article-views-date-x-grouped-date' => [
            'title'         => 'Number of article views ${date} grouped by date',
            'labels'        => 'kb',
            'description'   => '',
            'display_types' => 'table,simple_lines',
            'display_order' => 40,
            'query'         => '
SELECT DPQL_COUNT() AS \'Views\'
FROM articles
WHERE articles.views.date_created = ${date}
GROUP BY DPQL_ALIAS(DPQL_DATE(articles.views.date_created), \'Date\')',
            'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'average-chat-length-chats-created-group-x' => [
                'title'         => 'Average chat length for chats created ${date} grouped by ${chat}',
                'labels'        => 'chat',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 30,
                'query'         => '
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
                'query'         => '
SELECT AVG(UNIX_TIMESTAMP(tickets.date_first_agent_reply) - UNIX_TIMESTAMP(tickets.date_created)) / (60 * 60) AS \'Average Time (Hours)\', DPQL_COUNT() AS \'Total Tickets\'
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
                'query'         => '
SELECT AVG(UNIX_TIMESTAMP(tickets.date_resolved) - UNIX_TIMESTAMP(tickets.date_created)) / (60 * 60) AS \'Average Time (Hours)\', DPQL_COUNT() AS \'Total Tickets\'
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
                'query'         => '
SELECT AVG(tickets.total_user_waiting) / (60 * 60) AS \'Total Waiting Time (Hours)\', DPQL_COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = ${date}
GROUP BY ${ticket}',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets"},{"name":"date","type":"dates"}]',
            ],
        'community-views-date-x-grouped-date' => [
                'title'         => 'Number of community topics views ${date} grouped by date',
                'labels'        => 'community',
                'description'   => '',
                'display_types' => 'table,simple_lines',
                'display_order' => 40,
                'query'         => '
SELECT DPQL_COUNT() AS \'Views\'
FROM community_topics
WHERE community_topics.views.date_created = ${date}
GROUP BY DPQL_ALIAS(DPQL_DATE(community_topics.views.date_created), \'Date\')',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'most-active-tickets-status-created-date' => [
                'title'         => 'Most active tickets ${status} created ${date}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 210,
                'query'         => '
SELECT tickets_messages.ticket.subject, DPQL_COUNT() AS \'Messages\', tickets_messages.ticket.person, tickets_messages.ticket.department, tickets_messages.ticket.date_created, tickets_messages.ticket.agent
FROM tickets_messages
WHERE ${status} AND tickets_messages.ticket.date_created = ${date}
GROUP BY tickets_messages.ticket.id
ORDER BY DPQL_COUNT() DESC
LIMIT 100',
                'variables' => '[{"name":"status","type":"statuses","field_type":"tickets","table":"tickets_messages.ticket","default":"awaiting_agent"},{"name":"date","type":"dates"}]',
            ],
        'most-popular-email-domains-ticket-usage' => [
                'title'         => 'Most popular email domains by ticket usage',
                'labels'        => 'person',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 0,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Uses\'
FROM tickets
GROUP BY tickets.person_email.email_domain
ORDER BY DPQL_COUNT() DESC
LIMIT 100',
                'variables' => '[]',
            ],
        'most-popular-people-email-domains' => [
                'title'         => 'Most popular primary email domains',
                'labels'        => 'person',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 0,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total People\'
FROM people
GROUP BY people.primary_email.email_domain
ORDER BY DPQL_COUNT() DESC
LIMIT 100',
                'variables' => '[]',
            ],
        'number-article-com-created-date-group-by-article' => [
                'title'         => 'Number of article comments created ${date} grouped by article ${article}',
                'labels'        => 'kb',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 30,
                'query'         => '
SELECT DPQL_COUNT() AS \'Comments Created\'
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
                'query'         => '
SELECT DPQL_COUNT() AS \'Comments Created\'
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
                'query'         => '
SELECT DPQL_COUNT() AS \'Entries Created\'
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
                'query'         => '
SELECT DPQL_COUNT() AS \'Chats Created\'
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
                'query'         => '
SELECT DPQL_COUNT() AS \'Chats Created\'
FROM chat_conversations
WHERE chat_conversations.date_created = ${date} AND chat_conversations.is_agent = 0 AND chat_conversations.agent_id = NULL
GROUP BY ${chat}',
                'variables' => '[{"name":"chat","type":"fields","field_type":"chats","table":"chat_conversations","default":"department"},{"name":"date","type":"dates"}]',
            ],
        'number-community-created-date-group-by-ct' => [
                'title'         => 'Number of community topics comments created ${date} grouped by topic ${topic}',
                'labels'        => 'community',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 30,
                'query'         => '
SELECT DPQL_COUNT() AS \'Comments Created\'
FROM community_topic_comments
WHERE community_topic_comments.date_created = ${date}
GROUP BY ${topic}',
                'variables' => '[{"name":"topic","type":"fields","field_type":"community","table":"community_topics_comments.topic","default":"type"},{"name":"date","type":"dates"}]',
            ],
        'number-community-comments-created-date-group-by-x' => [
                'title'         => 'Number of community-topics comments created ${date} grouped by ${topic_comment}',
                'labels'        => 'community',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 20,
                'query'         => '
SELECT DPQL_COUNT() AS \'Comments Created\'
FROM community_topic_comments
WHERE community_topic_comments.date_created = ${date}
GROUP BY ${topic_comment}',
                'variables' => '[{"name":"topic_comment","type":"fields","field_type":"community_topic_comments","table":"community_topic_comments","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-community-created-date-group-by-x' => [
                'title'         => 'Number of community topics created ${date} grouped by ${topic}',
                'labels'        => 'community',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 10,
                'query'         => '
SELECT DPQL_COUNT() AS \'Entries Created\'
FROM community_topics
WHERE community_topics.date_created = ${date}
GROUP BY ${topic}',
                'variables' => '[{"name":"topic","type":"fields","field_type":"community","table":"community_topics","default":"type"},{"name":"date","type":"dates"}]',
            ],
        'number-community-votes-submitted-date-x-group-y' => [
                'title'         => 'Number of community topic votes submitted ${date} grouped by ${topic}',
                'labels'        => 'community',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 60,
                'query'         => '
SELECT DPQL_COUNT() AS \'Ratings\'
FROM community_topics
WHERE community_topics.ratings.date_created = ${date}
GROUP BY ${topic}',
                'variables' => '[{"name":"topic","type":"fields","field_type":"community","table":"community_topics","default":"type"},{"name":"date","type":"dates"}]',
            ],
        'number-ticket-messages-written-agent-day' => [
                'title'         => 'Number of ticket messages written ${date} per agent per [day]',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 140,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = ${date}
SPLIT BY tickets_messages.person
GROUP BY CONCAT(DPQL_YEAR(tickets_messages.date_created), \'-\', LPAD(DPQL_MONTHNAME(tickets_messages.date_created), 2, \'0\'), \'-\', LPAD(DPQL_DAYOFMONTH(tickets_messages.date_created), 2, \'0\')) AS \'Period\'
ORDER BY tickets_messages.date_created',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'number-ticket-messages-written-agent-month' => [
                'title'         => 'Number of ticket messages written ${date} per agent per [month]',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 140,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = ${date}
SPLIT BY tickets_messages.person
GROUP BY CONCAT(DPQL_YEAR(tickets_messages.date_created), \'-\', LPAD(DPQL_MONTHNAME(tickets_messages.date_created), 2, \'0\')) AS \'Period\'
ORDER BY tickets_messages.date_created',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'number-ticket-messages-written-agent-week' => [
                'title'         => 'Number of ticket messages written ${date} per agent per [week]',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 140,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = ${date}
SPLIT BY tickets_messages.person
GROUP BY CONCAT(\'Week \', WEEKOFYEAR(tickets_messages.date_created), \', \', DPQL_YEAR(tickets_messages.date_created)) AS \'Period\'
ORDER BY tickets_messages.date_created',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'number-ticket-messages-written-agent-year' => [
                'title'         => 'Number of ticket messages written ${date} per agent per [year]',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 140,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Messages\'
FROM tickets_messages
WHERE tickets_messages.person.is_agent = 1 AND tickets_messages.date_created = ${date}
SPLIT BY tickets_messages.person
GROUP BY DPQL_YEAR(tickets_messages.date_created) AS \'Period\'
ORDER BY DPQL_YEAR(tickets_messages.date_created) DESC',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'number-tickets-created-date-grouped-by-date-and-x' => [
                'title'         => 'Number of tickets created ${date} grouped by date created & ${ticket}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_area',
                'display_order' => 15,
                'query'         => '
SELECT DPQL_COUNT() AS \'Tickets Created\'
FROM tickets
WHERE tickets.date_created = ${date}
GROUP BY DPQL_ALIAS(DPQL_DATE(tickets.date_created), \'Date Created\'), ${ticket}',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-tickets-created-date-grouped-by-x-y' => [
                'title'         => 'Number of tickets created ${date} grouped by ${ticket} & ${ticket_2}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 10,
                'query'         => 'SELECT DPQL_COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = ${date}
GROUP BY DPQL_MATRIX(${ticket}, ${ticket_2})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"department"},{"name":"ticket_2","type":"fields","field_type":"tickets","table":"tickets","default":"agent"},{"name":"date","type":"dates"}]',
            ],
        'tickets-created-x-date-grouped-by-department' => [
            'title'         => 'Number of tickets created ${date} grouped by department',
            'labels'        => 'tickets',
            'description'   => '',
            'display_types' => 'simple_bars,pie,simple_area,simple_lines',
            'display_order' => 10,
            'query'         => 'SELECT DPQL_COUNT() AS \'Total Tickets\', tickets.department.title AS \'Department title\'
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
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = ${date}
GROUP BY DPQL_MATRIX(DPQL_ALIAS(DPQL_DATE_OFFSET_GROUP(DPQL_NOW(), tickets.date_first_agent_reply), \'Time Waiting\'), ${ticket})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-tickets-resolved-date-grouped-time-res-x' => [
                'title'         => 'Number of tickets resolved ${date} grouped by time to resolution & ${ticket}',
                'labels'        => 'tickets,resolved',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 80,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = ${date}
GROUP BY DPQL_MATRIX(DPQL_ALIAS(DPQL_DATE_OFFSET_GROUP(tickets.date_resolved, tickets.date_created), \'Time To Resolve\'), ${ticket})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-tickets-resolved-date-grouped-total-wait-x' => [
                'title'         => 'Number of tickets resolved ${date} grouped by total waiting time & ${ticket}',
                'labels'        => 'tickets,resolved',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 70,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'resolved\', \'archived\') AND tickets.date_resolved = ${date}
GROUP BY DPQL_MATRIX(DPQL_ALIAS(DPQL_DATE_OFFSET_GROUP(tickets.total_user_waiting), \'Time Waiting\'), ${ticket})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"},{"name":"date","type":"dates"}]',
            ],
        'number-tickets-resolved-date-grouped-x-y' => [
                'title'         => 'Number of tickets resolved ${date} grouped by ${ticket} & ${ticket_2}',
                'labels'        => 'tickets,resolved',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 30,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_resolved = ${date} AND tickets.status IN (\'resolved\', \'archived\')
GROUP BY DPQL_MATRIX(${ticket}, ${ticket_2})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"department"},{"name":"ticket_2","type":"fields","field_type":"tickets","table":"tickets","default":"agent"},{"name":"date","type":"dates"}]',
            ],
        'number-tickets-status-grouped-by-x-y' => [
                'title'         => 'Number of tickets ${status} grouped by ${ticket} & ${ticket_2}',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 20,
                'query'         => '
SELECT DPQL_COUNT() AS \'Tickets\'
FROM tickets
WHERE ${status}
GROUP BY DPQL_MATRIX(${ticket}, ${ticket_2})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"department"},{"name":"ticket_2","type":"fields","field_type":"tickets","table":"tickets","default":"agent"},{"name":"status","type":"statuses","field_type":"tickets","table":"tickets","default":"awaiting_agent"}]',
            ],
        'number-tickets-wait-agent-grouped-time-wait-ag-x' => [
                'title'         => 'Number of tickets awaiting agent grouped by time awaiting agent and ${ticket}',
                'labels'        => 'tickets,agents',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 50,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\')
GROUP BY DPQL_MATRIX(DPQL_ALIAS(DPQL_DATE_OFFSET_GROUP(DPQL_NOW(), tickets.date_user_waiting), \'Time Waiting\'), ${ticket})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"}]',
            ],
        'number-tickets-wait-agent-grouped-total-wait-x' => [
                'title'         => 'Number of tickets awaiting agent grouped by total waiting time and ${ticket}',
                'labels'        => 'tickets,agents',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 60,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\')
GROUP BY DPQL_MATRIX(DPQL_ALIAS(DPQL_DATE_OFFSET_GROUP(tickets.total_user_waiting), \'Time Waiting\'), ${ticket})',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"none"}]',
            ],
        'number-views-per-article-date-x' => [
                'title'         => 'Number of views per article ${date}',
                'labels'        => 'kb',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 50,
                'query'         => '
SELECT articles.title, DPQL_COUNT() AS \'Views\'
FROM articles
WHERE articles.views.date_created = ${date}
GROUP BY articles.id
ORDER BY DPQL_COUNT() DESC',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'number-views-per-community-topic-date-x' => [
                'title'         => 'Number of views per community topic ${date}',
                'labels'        => 'community,agents',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 50,
                'query'         => '
SELECT community_topics.title, DPQL_COUNT() AS \'Views\'
FROM community_topics
WHERE community_topics.views.date_created = ${date}
GROUP BY community_topics.id
ORDER BY DPQL_COUNT() DESC',
                'variables' => '[{"name":"date","type":"dates"}]',
            ],
        'organizations-longest-total--wait' => [
                'title'         => '[Organizations] with longest total waiting time',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 200,
                'query'         => '
SELECT SUM(tickets.total_user_waiting) / (60 * 60) AS \'Total Wait (Hours)\', DPQL_COUNT() AS \'Total Tickets\', AVG(tickets.total_user_waiting) / (60 * 60) AS \'Average Wait (Hours)\'
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
                'query'         => '
SELECT SUM(tickets.total_to_first_reply) / (60 * 60) AS \'Total Wait (Hours)\', DPQL_COUNT() AS \'Total Tickets\', AVG(tickets.total_to_first_reply) / (60 * 60) AS \'Average Wait (Hours)\'
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
                'query'         => '
SELECT SUM(tickets.total_user_waiting) / (60 * 60) AS \'Total Wait (Hours)\', DPQL_COUNT() AS \'Total Tickets\', AVG(tickets.total_user_waiting) / (60 * 60) AS \'Average Wait (Hours)\'
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
                'query'         => '
SELECT SUM(tickets.total_to_first_reply) / (60 * 60) AS \'Total Wait (Hours)\', DPQL_COUNT() AS \'Total Tickets\', AVG(tickets.total_to_first_reply) / (60 * 60) AS \'Average Wait (Hours)\'
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
                'query'         => '
SELECT DPQL_PERCENT(tickets.date_resolved <> NULL AND UNIX_TIMESTAMP(tickets.date_resolved) - UNIX_TIMESTAMP(tickets.date_created) < 24 * 60 * 60) AS \'Percentage\'
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
                'query'         => '
SELECT DPQL_PERCENT(tickets.total_to_first_reply < 3600) AS \'Percentage\'
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
                'query'         => '
SELECT DPQL_PERCENT(tickets.count_agent_replies = 1) AS \'Percentage\'
FROM tickets
WHERE tickets.date_created = ${date} AND tickets.date_resolved <> NULL AND tickets.count_agent_replies > 0
GROUP BY ${ticket}',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"agent"},{"name":"date","type":"dates"}]',
            ],
        'sla-date-groupby-x' => [
                'title'         => 'SLA Statuses for tickets created ${date} grouped by ${ticket}',
                'labels'        => 'sla,tickets',
                'description'   => '',
                'display_types' => 'table,simple_bars,pie,simple_area,simple_lines',
                'display_order' => 96,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.date_created = ${date} AND tickets.ticket_slas.sla_status IN (\'ok\', \'warning\', \'fail\')
GROUP BY ${ticket}, tickets.ticket_slas.sla_status',
                'variables' => '[{"name":"ticket","type":"fields","field_type":"tickets","table":"tickets","default":"agent"},{"name":"date","type":"dates"}]',
            ],
        'sla-status-date-splitby-x' => [
                'title'         => 'SLA Statuses for tickets created ${date} split by ${ticket}',
                'labels'        => 'sla,tickets',
                'description'   => '',
                'display_types' => 'table,pie',
                'display_order' => 95,
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Tickets\'
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
WHERE tickets.status IN (\'awaiting_agent\', \'pending\')
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
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Tickets\'
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
                'query'         => '
SELECT DPQL_COUNT_DISTINCT(tickets_logs.ticket_id) AS \'Tickets Resolved\'
FROM tickets_logs
WHERE tickets_logs.action_type = \'changed_status\' AND tickets_logs.id_after = 200 AND tickets_logs.ticket.status IN (\'resolved\', \'archived\') AND tickets_logs.date_created = ${date}
GROUP BY DPQL_ALIAS(IF(tickets_logs.person.is_agent, tickets_logs.person, \'Non-Agent\'), \'Person\')
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
                'query'         => '
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
                'query'         => '
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\')
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
                'query'         => '
SELECT DPQL_COUNT() AS \'Total\', DPQL_COUNT(tickets.status IN (\'awaiting_agent\', \'pending\')) AS \'Total Awaiting Agent\', DPQL_COUNT(tickets.status = \'awaiting_user\') AS \'Total Awaiting User\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\') AND tickets.date_created < %PAST_7_DAYS%',
                'variables' => '[]',
            ],
        'unresolved-high-priority-tickets' => [
                'title'         => 'Unresolved [high priority] tickets',
                'labels'        => 'tickets',
                'description'   => '',
                'display_types' => 'table',
                'display_order' => 230,
                'query'         => '
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent, tickets.priority
FROM tickets
WHERE tickets.priority.priority = 1 AND tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\')
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
                'query'         => '
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent, tickets.count_agent_replies AS \'Agent Replies\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\') AND tickets.count_agent_replies >= 10
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
                'query'         => '
SELECT tickets.id, tickets.subject, tickets.person, tickets.department, tickets.date_created, tickets.agent, tickets.urgency
FROM tickets
WHERE tickets.urgency > 7 AND tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\')
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
                'query'         => '
SELECT DPQL_COUNT() AS \'Total Tickets\'
FROM tickets
WHERE tickets.status IN (\'awaiting_agent\', \'pending\', \'awaiting_user\')
GROUP BY tickets.person
ORDER BY DPQL_COUNT() DESC
LIMIT 100',
                'variables' => '[]',
            ],
        'voice-calls-grouped-by-x' => [
            'title'         => 'Number of voice calls ${date} grouped by ${field}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 250,
            'query'         => '
SELECT DPQL_COUNT() AS \'Total Voice Calls\'
FROM voice_phone_calls
WHERE voice_phone_calls.date_created = ${date}
GROUP BY ${field}
ORDER BY DPQL_COUNT() DESC
LIMIT 100
            ',
            'variables' => '[{"name":"date","type":"dates"}, {"name":"field","type":"fields","field_type":"voice_phone_calls","table":"voice_phone_calls"}]',
        ],
        // BILLING REPORTS
        'list-charges-date' => [
            'title'         => 'List of charges ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 300,
            'query'         => '
                SELECT
                  DPQL_TOTAL(DPQL_TIME_LENGTH(ticket_charges.charge_time)) AS \'Time\',
                  DPQL_TOTAL(DPQL_FORMAT(ticket_charges.amount, \'number\', 2)) AS \'Amount (${billingCurrency})\', ${billingSelectBits} ticket_charges.agent, ticket_charges.date_created, ticket_charges.ticket
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date}
                ORDER BY ticket_charges.date_created',
            'variables' => '[{"name":"date","type":"dates", "default": "today"}]',
        ],
        'total-charges-per-day-date' => [
            'title'         => 'Total [charges] per day ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 301,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_COUNT()) AS \'Number of Charges\',
                    DPQL_TOTAL(DPQL_TIME_LENGTH(SUM(ticket_charges.charge_time))) AS \'Total Time\',
                    DPQL_TOTAL(DPQL_FORMAT(SUM(ticket_charges.amount), \'number\', 2)) AS \'Total Amount(${billingCurrency})\'
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date}
                GROUP BY DPQL_DATE(ticket_charges.date_created) AS \'Date\'
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-amount-charges-per-day-date' => [
            'title'         => 'Total [amount charges] per day ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table,simple_bars',
            'display_order' => 302,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_FORMAT(SUM(ticket_charges.amount), \'number\', 2)) AS \'Total Amount (${billingCurrency})\'
                    FROM ticket_charges
                    WHERE ticket_charges.date_created = ${date} AND ticket_charges.amount > 0
                    GROUP BY DPQL_DATE(ticket_charges.date_created) AS \'Date\'
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-time-charges-per-day-date' => [
            'title'         => 'Total [time charges] per day ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table,simple_bars',
            'display_order' => 303,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_TIME_LENGTH(SUM(ticket_charges.charge_time))) AS \'Total Time\'
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date} AND ticket_charges.charge_time > 0
                GROUP BY DPQL_DATE(ticket_charges.date_created) AS \'Date\'
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-charges-person-date' => [
            'title'         => 'Total [charges] per person ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 304,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_COUNT()) AS \'Number of Charges\',
                    DPQL_TOTAL(DPQL_TIME_LENGTH(SUM(ticket_charges.charge_time))) AS \'Total Time\',
                    DPQL_TOTAL(DPQL_FORMAT(SUM(ticket_charges.amount), \'number\', 2)) AS \'Total Amount (${billingCurrency})\'
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date}
                GROUP BY ticket_charges.person
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-amount-charges-person-date' => [
            'title'         => 'Total [amount charges] per person ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table,simple_Bars',
            'display_order' => 305,
            'query'         => '
                SELECT
                  DPQL_TOTAL(DPQL_FORMAT(SUM(ticket_charges.amount), \'number\', 2)) AS \'Total Amount (${billingCurrency})\'
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date} AND ticket_charges.amount > 0
                GROUP BY ticket_charges.person
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-time-charges-person-date' => [
            'title'         => 'Total [time charges] per person ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table,simple_bars',
            'display_order' => 306,
            'query'         => '
              SELECT DPQL_TOTAL(DPQL_TIME_LENGTH(SUM(ticket_charges.charge_time))) AS \'Total Time\'
              FROM ticket_charges
              WHERE ticket_charges.date_created = ${date} AND ticket_charges.charge_time > 0
              GROUP BY ticket_charges.person
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'list-charges-person-date' => [
            'title'         => 'List of charges per person ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 307,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_TIME_LENGTH(ticket_charges.charge_time)) AS \'Time\',
                    DPQL_TOTAL(DPQL_FORMAT(ticket_charges.amount, \'number\', 2)) AS \'Amount (${billingCurrency})\',
                    ${billingSelectBits} ticket_charges.agent, ticket_charges.date_created, ticket_charges.ticket
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date}
                SPLIT BY ticket_charges.person
                ORDER BY ticket_charges.date_created
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-charges-organization-date' => [
            'title'         => 'Total [charges] per organization ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 308,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_COUNT()) AS \'Number of Charges\',
                    DPQL_TOTAL(DPQL_TIME_LENGTH(SUM(ticket_charges.charge_time))) AS \'Total Time\',
                    DPQL_TOTAL(DPQL_FORMAT(SUM(ticket_charges.amount), \'number\', 2)) AS \'Total Amount (${billingCurrency})\'
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date} AND ticket_charges.organization_id <> NULL
                GROUP BY ticket_charges.organization
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-amount-charges-organization-date' => [
            'title'         => 'Total [amount charges] per organization ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table,simple_bars',
            'display_order' => 309,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_FORMAT(SUM(ticket_charges.amount), \'number\', 2)) AS \'Total Amount (${billingCurrency})\'
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date}
                  AND ticket_charges.organization_id <> NULL
                  AND ticket_charges.amount > 0
                GROUP BY ticket_charges.organization
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-time-charges-organization-date' => [
            'title'         => 'Total [time charges] per organization ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table,simple_bars',
            'display_order' => 310,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_TIME_LENGTH(SUM(ticket_charges.charge_time))) AS \'Total Time\'
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date}
                  AND ticket_charges.organization_id <> NULL
                  AND ticket_charges.charge_time > 0
                GROUP BY ticket_charges.organization
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'list-charges-organization-date' => [
            'title'         => 'List of charges per organization ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 311,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_TIME_LENGTH(ticket_charges.charge_time)) AS \'Time\',
                    DPQL_TOTAL(DPQL_FORMAT(ticket_charges.amount, \'number\', 2)) AS \'Amount (${billingCurrency})\',
                    ${billingSelectBits} ticket_charges.agent, ticket_charges.date_created, ticket_charges.ticket
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date} AND ticket_charges.organization_id <> NULL
                SPLIT BY ticket_charges.organization
                ORDER BY ticket_charges.date_created
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-charges-agent-date' => [
            'title'         => 'Total [charges] per agent ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 312,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_COUNT()) AS \'Number of Charges\',
                    DPQL_TOTAL(DPQL_TIME_LENGTH(SUM(ticket_charges.charge_time))) AS \'Total Time\',
                    DPQL_TOTAL(DPQL_FORMAT(SUM(ticket_charges.amount), \'number\', 2)) AS \'Total Amount (${billingCurrency})\'
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date}
                GROUP BY ticket_charges.agent
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-amount-charges-agent-date' => [
            'title'         => 'Total [amount charges] per agent ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table,simple_bars',
            'display_order' => 313,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_FORMAT(SUM(ticket_charges.amount), \'number\', 2)) AS \'Total Amount (${billingCurrency})\'
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date} AND ticket_charges.amount > 0
                GROUP BY ticket_charges.agent
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'total-time-charges-agent-date' => [
            'title'         => 'Total [time charges] per agent ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table,simple_bars',
            'display_order' => 314,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_TIME_LENGTH(SUM(ticket_charges.charge_time))) AS \'Total Time\'
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date} AND ticket_charges.charge_time > 0
                GROUP BY ticket_charges.agent
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'list-charges-agent-date' => [
            'title'         => 'List of charges per agent ${date}',
            'labels'        => 'billing',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 315,
            'query'         => '
                SELECT
                    DPQL_TOTAL(DPQL_TIME_LENGTH(ticket_charges.charge_time)) AS \'Time\',
                    DPQL_TOTAL(DPQL_FORMAT(ticket_charges.amount, \'number\', 2)) AS \'Amount (${billingCurrency})\',
                    ${billingSelectBits} ticket_charges.date_created, ticket_charges.ticket
                FROM ticket_charges
                WHERE ticket_charges.date_created = ${date}
                SPLIT BY ticket_charges.agent
                ORDER BY ticket_charges.date_created
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-active-calls' => [
            'title'         => 'Active Calls',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 316,
            'query'         => '
                SELECT DPQL_COUNT() AS \'stat_value\', \'Active Calls\' AS \'stat_description\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.status IN (\'warm_add\', \'warm_transfer\', \'cold_transfer\', \'active\')
            ',
            'variables' => '[]',
        ],
        'voice-callers-waiting' => [
            'title'         => 'Callers Waiting',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 317,
            'query'         => '
                SELECT DPQL_COUNT() AS \'stat_value\', \'Waiting Callers\' AS \'stat_description\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.status = \'pending\'
            ',
            'variables' => '[]',
        ],
        'voice-online-agents' => [
            'title'         => 'Online Voice Agents',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 318,
            'query'         => '
                SELECT DPQL_COUNT_DISTINCT(sessions.person.id) AS \'stat_value\', IF(DPQL_COUNT_DISTINCT(sessions.person.id) = 1, \'Voice Agent Online\', \'Voice agents online\') AS \'stat_description\'
                FROM sessions
                WHERE sessions.date_last > DPQL_NOW() - INTERVAL 1 MINUTE
                AND sessions.person.is_agent = 1
                AND sessions.person.is_disabled = 0
                AND sessions.person.is_deleted = 0
                AND sessions.person.agent_data.agent_calls_enabled = 1
            ',
            'variables' => '[]',
        ],
        'voice-forwarding-agents' => [
            'title'         => 'Forwarding Voice Agents',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 319,
            'query'         => '
                SELECT DPQL_COUNT_DISTINCT(agent_data.person.id) AS \'stat_value\', \'Forwarding Agents\' AS \'stat_description\'
                FROM agent_data
                WHERE agent_data.agent_can_use_forwarding = 1
                AND agent_data.person.is_agent = 1
                AND agent_data.person.is_disabled = 0
                AND agent_data.person.is_deleted = 0
                AND agent_data.person.id NOT IN (
                    SELECT sessions.person_id
                    FROM sessions
                    WHERE sessions.date_last > DPQL_NOW() - INTERVAL 1 MINUTE
                )
            ',
            'variables' => '[]',
        ],
        'voice-online-and-forwarding-agents-per-queue' => [
            'title'         => 'Online & Forwarding Agents per Queue',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_bars',
            'display_order' => 320,
            'query'         => '
                SELECT DPQL_COUNT(voice_queue_agents.is_enabled = 1) AS \'Voice agents online\'
                FROM voice_queue_agents
                WHERE  voice_queue_agents.agent.agent_data.agent_calls_enabled = 1
                AND agent_data.person.is_agent = 1
                AND agent_data.person.is_disabled = 0
                AND agent_data.person.is_deleted = 0
                AND voice_queue_agents.agent.id IN (
                    SELECT sessions.person.id
                    FROM sessions
                    WHERE sessions.date_last > DPQL_NOW() - INTERVAL 1 MINUTE
                )
                GROUP BY voice_queue_agents.queue
                LAYER WITH
                SELECT DPQL_COUNT(voice_queue_agents.is_enabled = 1) AS \'Forwarding Agents\'
                FROM voice_queue_agents
                WHERE voice_queue_agents.agent.agent_data.agent_can_use_forwarding = 1
                AND agent_data.person.is_agent = 1
                AND agent_data.person.is_disabled = 0
                AND agent_data.person.is_deleted = 0
                AND voice_queue_agents.agent.id NOT IN (
                    SELECT sessions.person_id
                    FROM sessions
                    WHERE sessions.date_last > DPQL_NOW() - INTERVAL 1 MINUTE
                )
                GROUP BY voice_queue_agents.queue
            ',
            'variables' => '[]',
        ],
        'voice-inbound-and-outbound-calls-today' => [
            'title'         => 'Inbound & Outbound Calls made %today%',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_bars',
            'display_order' => 321,
            'query'         => '
                SELECT DPQL_COUNT(voice_phone_calls.date_ended = %TODAY%) AS \'Inbound\', \'Calls\' AS \'value_axis_title\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.type = \'Inbound\' AND voice_phone_calls.date_waiting = NULL
                GROUP BY DPQL_HOUR(voice_phone_calls.date_ended, 0, 23) AS \'HOUR\'
                LAYER WITH
                SELECT DPQL_COUNT(voice_phone_calls.date_ended = %TODAY%) AS \'Outbound\', \'Calls\' AS \'value_axis_title\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.type = \'Outbound\'
                GROUP BY DPQL_HOUR(voice_phone_calls.date_ended, 0, 23) AS \'HOUR\'
            ',
            'variables' => '[]',
        ],
        'voice-answered-calls' => [
            'title'         => 'Answered Calls ${date}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 322,
            'query'         => '
                SELECT DPQL_COUNT() AS \'stat_value\', \'Answered Calls\' AS \'stat_description\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.date_ended = ${date}
                AND voice_phone_calls.date_started <> NULL
                AND voice_phone_calls.type = \'Inbound\'
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-missed-calls' => [
            'title'         => 'Missed Calls ${date}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 323,
            'query'         => '
                SELECT DPQL_COUNT() AS \'stat_value\', \'Missed Calls\' AS \'stat_description\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.date_ended = ${date}
                AND voice_phone_calls.date_started = NULL
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-voicemail-calls' => [
            'title'         => 'Voicemail Calls ${date}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 324,
            'query'         => '
                SELECT DPQL_COUNT() AS \'stat_value\', \'Voicemail Calls\' AS \'stat_description\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.date_ended = ${date}
                AND voice_phone_calls.status = \'voicemail\'
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-outbound-calls' => [
            'title'         => 'Outbound Calls ${date}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 325,
            'query'         => '
                SELECT DPQL_COUNT() AS \'stat_value\', \'Outbound Calls\' AS \'stat_description\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.date_ended = ${date}
                AND voice_phone_calls.date_started <> NULL
                AND voice_phone_calls.type = \'outbound\'
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-average-inbound-wait-time' => [
            'title'         => 'Average Inbound Wait Time ${date}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 326,
            'query'         => '
                SELECT IFNULL(AVG(UNIX_TIMESTAMP(voice_phone_calls.date_started) - UNIX_TIMESTAMP(voice_phone_calls.date_created)) / 60, \'0\') AS \'stat_value\', \'Avg Inbound Wait Time (min)\' AS \'stat_description\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.type = \'inbound\'
                AND voice_phone_calls.date_started <> NULL
                AND voice_phone_calls.date_ended = ${date}
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-average-inbound-duration' => [
            'title'         => 'Average Inbound Duration ${date}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 327,
            'query'         => '
                SELECT IFNULL(AVG(UNIX_TIMESTAMP(voice_phone_calls.date_ended) - UNIX_TIMESTAMP(voice_phone_calls.date_started)) / 60, \'0\') AS \'stat_value\', \'Avg Inbound Duration (min)\' AS \'stat_description\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.type = \'inbound\'
                AND voice_phone_calls.date_started <> NULL
                AND voice_phone_calls.date_ended = ${date}
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-average-outbound-duration' => [
            'title'         => 'Average Outbound Duration ${date}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_stat',
            'display_order' => 328,
            'query'         => '
                SELECT IFNULL(AVG(UNIX_TIMESTAMP(voice_phone_calls.date_ended) - UNIX_TIMESTAMP(voice_phone_calls.date_started)) / 60, \'0\') AS \'stat_value\', \'Avg Outbound Duration (min)\' AS \'stat_description\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.type = \'outbound\'
                AND voice_phone_calls.date_started <> NULL
                AND voice_phone_calls.date_ended = ${date}
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-queue-calls-per-department' => [
            'title'         => 'Queue Calls per Department ${date}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'simple_bars',
            'display_order' => 329,
            'query'         => '
                SELECT DPQL_COUNT() AS \'Calls\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.phone_call_logs.target_queue.department <> NULL
                AND voice_phone_calls.date_created = ${date}
                GROUP BY voice_phone_calls.phone_call_logs.target_queue.department
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-agent-data' => [
            'title'         => 'Voice Agent Data',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 330,
            'query'         => '
                SELECT
                    DPQL_COUNT(voice_phone_call_participants.person_id) AS \'Calls Handled\',
                    IFNULL(AVG(UNIX_TIMESTAMP(voice_phone_call_participants.date_left) - UNIX_TIMESTAMP(voice_phone_call_participants.date_created)) / 60, \'0\') AS \'Average Call Length (min)\',
                    IFNULL(SUM(UNIX_TIMESTAMP(voice_phone_call_participants.date_left) - UNIX_TIMESTAMP(voice_phone_call_participants.date_created)) / 60, \'0\') AS \'Total Call Time(min)\'
                FROM voice_phone_call_participants
                WHERE voice_phone_call_participants.person_id IN (
                    SELECT people.id
                    FROM people
                    WHERE people.is_agent = 1
                )
                AND voice_phone_call_participants.date_left = ${date}
                GROUP BY voice_phone_call_participants.person
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-queue-data' => [
            'title'         => 'Queue Data ${date}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 331,
            'query'         => '
                SELECT
                    DPQL_COUNT(voice_phone_calls.id) AS \'Total Calls\',
                    DPQL_COUNT(voice_phone_calls.date_started <> NULL AND voice_phone_calls.status = \'ended\') AS \'Answered Calls\',
                    DPQL_COUNT(voice_phone_calls.date_started = NULL AND voice_phone_calls.status = \'ended\') AS \'Missed Calls\',
                    DPQL_COUNT(voice_phone_calls.status = \'voicemail\') AS \'Voicemail Calls\',
                    IFNULL(AVG(UNIX_TIMESTAMP(voice_phone_calls.date_started) - UNIX_TIMESTAMP(voice_phone_calls.date_created)) / 60, \'0\') AS \'Average Wait Time (min)\',
                    IFNULL(AVG(UNIX_TIMESTAMP(voice_phone_calls.date_ended) - UNIX_TIMESTAMP(voice_phone_calls.date_started)) / 60, \'0\') AS \'Average Call Length (min)\',
                    DPQL_FORMAT(SUM(voice_phone_calls.cost), 3) AS \'Cost (USD)\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.phone_call_logs.target_queue <> NULL
                AND voice_phone_calls.date_ended = ${date}
                AND voice_phone_calls.phone_call_logs.action_type = \'call.ended\'
                GROUP BY voice_phone_calls.phone_call_logs.target_queue
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'voice-frequent-callers' => [
            'title'         => 'Frequent Callers ${date}',
            'labels'        => 'voice',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 332,
            'query'         => '
                SELECT DPQL_COUNT() AS \'Calls\'
                FROM voice_phone_calls
                WHERE voice_phone_calls.date_created = ${date} AND voice_phone_calls.type = \'inbound\'
                GROUP BY voice_phone_calls.person
                ORDER BY @\'Callers\' DESC
            ',
            'variables' => '[{"name":"date","type":"dates", "default": "this_month"}]',
        ],
        'count-ticket-approvals-by-status' => [
            'title'         => 'Count of approvals that are ${status} grouped by ${grouping}',
            'labels'        => 'tickets,approvals',
            'description'   => '',
            'display_types' => 'table',
            'display_order' => 316,
            'query'         => '
                SELECT DPQL_COUNT() AS \'Ticket Approvals\'
                FROM ticket_approvals
                WHERE ${status}
                GROUP BY ${grouping}
            ',
            'variables' => '[{"type":"statuses","name":"status","table":"","default":"pending","field_type":"ticket_approvals"},{"type":"fields","name":"grouping","default":"type","field_type":"ticket_approvals"}]',
        ],
        'average-resolution-time-ticket-approvals' => [
            'title'         => 'Average resolution time for approvals created ${date} grouped by ${grouping}',
            'labels'        => 'tickets,approvals',
            'description'   => '',
            'display_types' => 'table,simple_bars',
            'display_order' => 317,
            'query'         => '
                SELECT COALESCE(AVG(UNIX_TIMESTAMP(IF(ticket_approvals.status = \'cancelled\', ticket_approvals.cancelled_at, ticket_approvals.completed_at)) - UNIX_TIMESTAMP(ticket_approvals.created_at)), 0) / (60 * 60) AS \'Average Time (Hours)\'
                FROM ticket_approvals
                WHERE ticket_approvals.created_at = ${date}
                GROUP BY ${grouping}
            ',
            'variables' => '[{"type":"dates","name":"date","default":"today"},{"name":"grouping","type":"fields","default":"type","field_type":"ticket_approvals"}]',
        ],
        'ticket-approval-responses-by-ballot' => [
            'title'         => 'Approval responses by ballot grouped by ${grouping} created ${date}',
            'labels'        => 'tickets,approvals',
            'description'   => '',
            'display_types' => 'simple_bars',
            'display_order' => 318,
            'query'         => '
                SELECT DPQL_COUNT(IF(approval_responses.vote = 1, 1, NULL)) AS \'Approvals\', \'Responses\' AS \'value_axis_title\'
                FROM approval_responses
                WHERE approval_responses.approval.created_at = ${date}
                GROUP BY ${grouping}

                LAYER WITH

                SELECT DPQL_COUNT(IF(approval_responses.vote = -1, 1, NULL)) AS \'Rejections\'
                FROM approval_responses
                WHERE approval_responses.approval.created_at = ${date}
                GROUP BY ${grouping}
            ',
            'variables' => '[{"type":"fields","name":"grouping","default":"approval_template","value":"","field_type":"ticket_approvals","field_value":"","table":"approval_responses.approval"},{"type":"dates","name":"date","default":"today"}]',
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
