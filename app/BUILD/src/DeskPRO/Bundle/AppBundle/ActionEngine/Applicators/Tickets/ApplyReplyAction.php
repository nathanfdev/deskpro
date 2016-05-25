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

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\ActionApplicatorInterface;
use DeskPRO\Bundle\AppBundle\DependencyInjection\SystemServices\EnvironmentService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ApplyReplyAction extends AbstractTicketApplicator implements ActionApplicatorInterface
{
    /** @var  TokenStorage */
    protected $tokenStorage;

    /** @var  EnvironmentService */
    protected $environmentService;

    /** @var  array */
    private $geoIp;

    public function __construct(EntityManager $em, TokenStorage $tokenStorage, EnvironmentService $environmentService)
    {
        parent::__construct($em);
        $this->tokenStorage       = $tokenStorage;
        $this->environmentService = $environmentService;
        $this->geoIp              = $environmentService->getGeoIp();
    }

    /**
     * @param Ticket $ticket
     */
    public function apply($ticket)
    {
        $message = new TicketMessage();
        $message
            ->setTicket($ticket)
            ->setMessage($this->options['reply']['message'])
            ->setPerson($this->tokenStorage->getToken()->getUser())
            ->setCreationSystem(TicketMessage::CREATED_WEB_AGENT)
            ->setHostname($this->environmentService->getHostname())
            ->setGeoCountry($this->getGeoCountry())
            ->setAsAgentNote($this->options['reply']['isAgentNote']);
        $this->saveTicket($ticket, 'mass_reply');
        $this->em->persist($message);
    }

    private function getGeoCountry()
    {
        return isset($this->geoIp['country_code']) ? $this->geoIp['country_code'] : null;
    }
}
