<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFlagged;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * Class PhpAgentHelper.
 */
class PhpAgentHelper extends AbstractPhpHelper
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param EntityManager   $em
     */
    public function __construct(LoggerInterface $logger, EntityManager $em)
    {
        parent::__construct($logger);
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'agent';
    }

    /**
     * @internal
     *
     * @param Ticket $ticket
     * @param int    $agentId
     *
     * @return array
     */
    public function getFlags(Ticket $ticket, $agentId)
    {
        $flags = $this->em->getRepository(TicketFlagged::class)->findBy([
            'ticket_id' => $ticket->getId(),
            'person_id' => $agentId,
        ]);

        $colors = [];
        foreach ($flags as $flag) {
            $colors[] = $flag->getColor();
        }

        return $colors;
    }
}
