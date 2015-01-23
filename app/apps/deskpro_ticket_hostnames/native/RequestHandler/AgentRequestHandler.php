<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace deskpro_ticket_hostnames\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\AgentRequestHandlerInterface;

class AgentRequestHandler implements AgentRequestHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public function handleAgentRequest(AgentRequestContext $context)
    {
        if ($context->getAction() == 'get-hostnames') {
            return $this->getHostnames($context);
        } elseif ($context->getAction() == 'refresh-hostname') {
            return $this->refreshHostname($context);
        } else {
            throw $context->createNotFoundException();
        }
    }

    private function refreshHostname(AgentRequestContext $context)
    {
        $ticket = $context->getContainer()->getTicketManager()->getTicket($context->getIn()->getUint('ticket_id'));
        $ip = $context->getIn()->getString('ip');

        if (!$ticket || !$ip) {
            throw $context->createNotFoundException();
        }

        $db = $context->getContainer()->getDb();

        // Delete cache for this ip
        $db->delete('cache', array('id' => 'ip2host--'.$ip));

        $rdns = $context->getContainer()->getSystemService('TicketMessageHostnameLookup')->getRdns();

        try {
            $hostname = $rdns->lookup($ip);
        } catch (\Exception $e) {
            return $context->createJsonResponse(array('nochange' => true));
        }

        $db->update('tickets_messages', array(
            'hostname' => $hostname
        ), array(
            'ticket_id' => $ticket->id,
            'ip_address' => $ip
        ));

        return $context->createJsonResponse(array('hostname' => $hostname));
    }

    /**
     * @param  AgentRequestContext                        $context
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getHostnames(AgentRequestContext $context)
    {
        $ticket = $context->getContainer()->getTicketManager()->getTicket($context->getIn()->getUint('ticket_id'));

        if (!$ticket) {
            throw $context->createNotFoundException();
        }

        $db = $context->getContainer()->getDb();

        if ($context->getAppSetting('rdns_ticket_showprops_agents')) {
            $message_info = $db->fetchAll("
                SELECT
                  tickets_messages.id AS message_id,
                  tickets_messages.ip_address, tickets_messages.hostname, tickets_messages.person_id,
                  people.name AS person_name, people.first_name AS person_fname, people.last_name AS person_lname, people_emails.email AS person_email
                FROM tickets_messages
                LEFT JOIN tickets ON (tickets.id = tickets_messages.ticket_id)
                LEFT JOIN people ON (people.id = tickets_messages.person_id)
                LEFT JOIN people_emails ON (people_emails.id = people.primary_email_id)
                WHERE
                  tickets_messages.ticket_id = ?
                  AND tickets_messages.ip_address != ''
                ORDER BY tickets_messages.id ASC
            ", array($ticket->id));
        } else {
            $message_info = $db->fetchAll("
                SELECT
                  tickets_messages.id AS message_id,
                  tickets_messages.ip_address, tickets_messages.hostname, tickets_messages.person_id,
                  people.name AS person_name, people.first_name AS person_fname, people.last_name AS person_lname, people_emails.email AS person_email
                FROM tickets_messages
                LEFT JOIN tickets ON (tickets.id = tickets_messages.ticket_id)
                LEFT JOIN people ON (people.id = tickets_messages.person_id)
                LEFT JOIN people_emails ON (people_emails.id = people.primary_email_id)
                WHERE
                  tickets_messages.ticket_id = ?
                  AND tickets_messages.ip_address != ''
                  AND (tickets_messages.person_id = tickets.person_id OR people.is_agent = 0)
                ORDER BY tickets_messages.id ASC
            ", array($ticket->id));
        }

        $user_list = array();

        foreach ($message_info as $info) {
            if (!isset($user_list[$info['person_id']])) {
                if ($info['person_name']) {
                    $name = $info['person_name'];
                } else {
                    $name = trim($info['person_fname'] . ' ' . $info['person_lname']);
                }
                $user_list[$info['person_id']] = array(
                    'id'    => $info['person_id'],
                    'name'  => $name ?: $info['person_email'],
                    'email' => $info['person_email'],
                    'recs'  => array()
                );
            }

            $user_list[$info['person_id']]['recs'][] = array(
                'ip'         => $info['ip_address'],
                'hostname'   => $info['hostname'] ?: null,
                'message_id' => $info['message_id'],
            );
        }

        foreach ($user_list as &$user) {
            $ips = array();

            foreach ($user['recs'] as $info) {
                if (!isset($ips[$info['ip']]) || $ips[$info['ip']]['hostname'] === null || $ips[$info['ip']]['message_id'] < $info['message_id']) {
                    $ips[$info['ip']] = $info;
                }
            }

            $user['recs'] = array_values($ips);
        }

        $data = array(
            'ticket_id' => $ticket->id,
            'user_list' => array_values($user_list)
        );

        return $context->createJsonResponse($data);
    }
}
