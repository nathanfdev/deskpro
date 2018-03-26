<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_ticket_hostnames\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\AgentRequestHandlerInterface;

class AgentRequestHandler implements AgentRequestHandlerInterface
{
    /**
     * {@inheritdoc}
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
        $ip     = $context->getIn()->getString('ip');

        if (!$ticket || !$ip) {
            throw $context->createNotFoundException();
        }

        $db = $context->getContainer()->getDb();

        // Delete cache for this ip
        $db->delete('cache', ['id' => 'ip2host--'.$ip]);

        $rdns = $context->getContainer()->getSystemService('TicketMessageHostnameLookup')->getRdns();

        try {
            $hostname = $rdns->lookup($ip);
        } catch (\Exception $e) {
            return $context->createJsonResponse(['nochange' => true]);
        }

        $db->update('tickets_messages', [
            'hostname' => $hostname,
        ], [
            'ticket_id'  => $ticket->id,
            'ip_address' => $ip,
        ]);

        return $context->createJsonResponse(['hostname' => $hostname]);
    }

    /**
     * @param AgentRequestContext $context
     *
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
            ", [$ticket->id]);
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
            ", [$ticket->id]);
        }

        $user_list = [];

        foreach ($message_info as $info) {
            if (!isset($user_list[$info['person_id']])) {
                if ($info['person_name']) {
                    $name = $info['person_name'];
                } else {
                    $name = trim($info['person_fname'].' '.$info['person_lname']);
                }
                $user_list[$info['person_id']] = [
                    'id'    => $info['person_id'],
                    'name'  => $name ?: $info['person_email'],
                    'email' => $info['person_email'],
                    'recs'  => [],
                ];
            }

            $user_list[$info['person_id']]['recs'][] = [
                'ip'         => $info['ip_address'],
                'hostname'   => $info['hostname'] ?: null,
                'message_id' => $info['message_id'],
            ];
        }

        foreach ($user_list as &$user) {
            $ips = [];

            foreach ($user['recs'] as $info) {
                if (!isset($ips[$info['ip']]) || $ips[$info['ip']]['hostname'] === null || $ips[$info['ip']]['message_id'] < $info['message_id']) {
                    $ips[$info['ip']] = $info;
                }
            }

            $user['recs'] = array_values($ips);
        }

        $data = [
            'ticket_id' => $ticket->id,
            'user_list' => array_values($user_list),
        ];

        return $context->createJsonResponse($data);
    }
}
