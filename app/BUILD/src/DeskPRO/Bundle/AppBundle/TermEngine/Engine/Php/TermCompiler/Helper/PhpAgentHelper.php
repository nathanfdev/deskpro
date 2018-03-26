<?php

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
