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

namespace DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\Tickets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\ActionInitializationInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApplyAssignAction extends AbstractTicketApplicator implements ActionInitializationInterface
{
    /** @var array */
    private $collection;

    public function init()
    {
        foreach ($this->options['assign'] as $type => $id) {
            switch ($type) {
                case 'agent':
                    $agent = null;
                    if ($id) {
                        $agent = $this->em->getRepository(Person::class)->find($id);
                        if (!$agent) {
                            throw new BadRequestHttpException("Agent with ID=$id doesn't exists");
                        }
                    }
                    $this->collection['agent'] = $agent;
                    break;
                case 'team':
                    $team = null;
                    if ($id) {
                        $team = $this->em->getRepository(AgentTeam::class)->find($id);
                        if (!$team) {
                            throw new BadRequestHttpException("Agents team with ID=$id doesn't exists");
                        }
                    }
                    $this->collection['team'] = $team;
                    break;
                case 'department':
                    $department = null;
                    if ($id) {
                        $department = $this->em->getRepository(Department::class)->find($id);
                        if (!$department) {
                            throw new BadRequestHttpException("Department with ID=$id doesn't exists");
                        }
                    }
                    $this->collection['department'] = $department;
                    break;
            }
        }
    }

    /**
     * @param Ticket $ticket
     */
    public function apply($ticket)
    {
        foreach ($this->collection as $type => $value) {
            switch ($type) {
                case 'agent':
                    $ticket->setAgent($value);
                    break;
                case 'team':
                    $ticket->setAgentTeam($value);
                    break;
                case 'department':
                    $ticket->setDepartment($value);
                    break;
            }
        }
    }
}
