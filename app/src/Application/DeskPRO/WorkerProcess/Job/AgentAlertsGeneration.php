<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
 */
namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\Entity\Ticket;

class AgentAlertsGeneration extends AbstractJob
{
    const DEFAULT_INTERVAL = 600;

    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;
    private $agent;
    private $types = ['is_new_ticket', 'is_new_agent_reply', 'is_new_agent_note', 'is_new_user_reply'];

    /**
     * Run the task.
     */
    public function run()
    {
        /* @ToDo return check for dev mode later
         * if (!$GLOBALS['DP_CONFIG']['debug]['dev']) {
            return;
        }
        */
        $this->em         = $this->getContainer()->getEm();
        $dismissedCounter = $this->getDismissedCounter();
        if ($dismissedCounter > 100) {
            return;
        }
        $this->agent  = $this->getContainer()->getAgentData()->get(1);
        $amount       = rand(1, 8);
        $ticketIds    = $this->getRandomTicketsIds($amount);
        $performerIds = $this->getRandomPerformersIds($amount);
        foreach ($ticketIds as $id) {
            /** @var Ticket $ticket */
            $ticket = $this->em->getRepository('DeskPRO:Ticket')->find($id);
            if (null !== $ticket) {
                $vars        = $this->getVars($performerIds, $ticket);
                $alertData   = $this->prepareAlertData($vars, $ticket);
                $alertSender = $this->getContainer()->getAgentAlertSender();
                $alertSender->send($this->agent, 'tickets', $alertData);
            }
        }
    }

    /**
     * @return array
     */
    private function getDismissedCounter()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(alert)')
            ->from('DeskPRO:AgentAlert', 'alert')
            ->where('alert.is_dismissed = 0');
        $dismissedCounter = $qb->getQuery()->getSingleScalarResult();

        return $dismissedCounter;
    }

    /**
     * @param $amount
     *
     * @return array
     */
    private function getRandomTicketsIds($amount)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('ticket.id')
            ->from('DeskPRO:Ticket', 'ticket')
            ->groupBy('ticket.id');
        if ($amount > 1) {
            $ticketIds = array_rand($qb->getQuery()->getScalarResult(), $amount);
        } else {
            $ids       = $qb->getQuery()->getScalarResult();
            $key       = array_rand($ids, $amount);
            $ticketIds = $ids[$key];
        }

        return $ticketIds;
    }

    /**
     * @param $amount
     *
     * @return mixed
     */
    private function getRandomPerformersIds($amount)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('person.id')
            ->from('DeskPRO:Person', 'person')
            ->groupBy('person.id');
        $performerIds = array_rand($qb->getQuery()->getScalarResult(), $amount * 2);

        return $performerIds;
    }

    /**
     * @param $performerIds
     * @param $ticket
     *
     * @return array
     */
    private function getVars($performerIds, Ticket $ticket)
    {
        $typeKey      = array_rand($this->types, 1);
        $type         = $this->types[$typeKey];
        $performerKey = array_rand($performerIds, 1);
        $vars         = [
            'is_new_ticket'      => $type === 'is_new_ticket',
            'is_new_agent_reply' => $type === 'is_new_agent_reply',
            'is_new_agent_note'  => $type === 'is_new_agent_note',
            'is_new_user_reply'  => $type === 'is_new_user_reply',
            'ticket'             => $ticket,
            'performer'          => $performerIds[$performerKey],
            // 'log_items'          => $this->getActionOption('ticket_logs'),
        ];

        return $vars;
    }

    /**
     * @param $vars
     * @param $ticket
     *
     * @throws \Exception
     * @throws null
     *
     * @return array
     */
    private function prepareAlertData($vars, Ticket $ticket)
    {
        $tpl_line  = $this->getTplLine($vars);
        $alertData = [
            '@fetch_types' => [
                'ticket'    => 'DeskPRO:Ticket',
                'performer' => 'DeskPRO:Person',
                'log_items' => 'DeskPRO:TicketLog',
            ],
            'ticket'             => $ticket->getId(),
            'performer'          => $vars['performer'],
            'is_new_ticket'      => $vars['is_new_ticket'],
            'is_new_agent_reply' => $vars['is_new_agent_reply'],
            'is_new_agent_note'  => $vars['is_new_agent_note'],
            'is_new_user_reply'  => $vars['is_new_user_reply'],
            'browser_rendered'   => $tpl_line,
            // 'log_items'          => $log_ids,
        ];

        return $alertData;
    }

    /**
     * @param $vars
     *
     * @throws \Exception
     * @throws null
     *
     * @return mixed
     */
    private function getTplLine($vars)
    {
        $tpl      = $this->getContainer()->getTemplating();
        $tr       = $this->getContainer()->getTranslator();
        $tpl_line = $tr->callWithPersonContext(
            $this->agent,
            function () use ($tpl, $vars) {
                return $tpl->render('AgentBundle:TicketSearch:notify-row.html.twig', $vars);
            }
        );

        return $tpl_line;
    }
}
