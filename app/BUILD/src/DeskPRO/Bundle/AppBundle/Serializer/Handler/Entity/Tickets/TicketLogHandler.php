<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets;

use Application\DeskPRO\Entity\TicketLog as TicketLogEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketLog;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class TicketLogHandler.
 */
class TicketLogHandler extends AbstractEntityHandler
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * Constructor.
     *
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return TicketLogEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketLogEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $messageHtml = $this->twig->render('AgentBundle:Ticket:ticket-log-actiontext.html.twig', [
            'log'    => $entity,
            'ticket' => $entity->getTicket(),
        ]);

        return new TicketLog($entity, $messageHtml);
    }
}
