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
 */
namespace DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\Tickets;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\ActionApplicatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApplyAssignAction extends AbstractTicketApplicator implements ActionApplicatorInterface
{
    /**
     * @param Ticket[] $tickets
     */
    public function apply(array $tickets)
    {
        $collection = $this->init();
        $context    = $this->tm->createAgentExecutorContext(null, 'set_status', 'mass_actions');

        foreach ($collection as $type => $value) {
            switch ($type) {
                case 'agent':
                    foreach ($tickets as $ticket) {
                        $ticket->setAgent($value);
                        $this->tm->saveTicket($ticket, $context);
                    }
                    break;
                case 'team':
                    foreach ($tickets as $ticket) {
                        $ticket->setAgentTeam($value);
                        $this->tm->saveTicket($ticket, $context);
                    }
                    break;
                case 'department':
                    foreach ($tickets as $ticket) {
                        $ticket->setDepartment($value);
                        $this->tm->saveTicket($ticket, $context);
                    }
                    break;
            }
        }
    }

    /**
     * @return array
     */
    private function init()
    {
        $collection = [];

        foreach ($this->options['assign'] as $type => $id) {
            switch ($type) {
                case 'agent':
                    $agent = null;
                    if ($id) {
                        $agent = $this->em->getRepository('DeskPRO:Person')->find($id);
                        if (!$agent) {
                            throw new BadRequestHttpException("Agent with ID=$id doesn't exists");
                        }
                    }
                    $collection['agent'] = $agent;
                    break;
                case 'team':
                    $team = null;
                    if ($id) {
                        $team = $this->em->getRepository('DeskPRO:AgentTeam')->find($id);
                        if (!$team) {
                            throw new BadRequestHttpException("Agents team with ID=$id doesn't exists");
                        }
                    }
                    $collection['team'] = $team;
                    break;
                case 'department':
                    $department = null;
                    if ($id) {
                        $department = $this->em->getRepository('DeskPRO:Department')->find($id);
                        if (!$department) {
                            throw new BadRequestHttpException("Department with ID=$id doesn't exists");
                        }
                    }
                    $collection['department'] = $department;
                    break;
            }
        }

        return $collection;
    }
}
