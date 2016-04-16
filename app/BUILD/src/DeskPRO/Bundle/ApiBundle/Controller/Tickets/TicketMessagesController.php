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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketMessageType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketMessageController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tickets/{parentId}/messages")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\TicketMessage")
 */
class TicketMessagesController extends CrudSubController
{
    public static $entity         = TicketMessage::class;
    public static $type           = TicketMessageType::class;
    public static $parentProperty = 'ticket';
    public static $sortOptions    = ['date' => 'date_created'];
    public static $listSort       = 'id';
    public static $listOrder      = 'asc';

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'ticket'          => $this->findParentOr404(),
            'person'          => $this->getUser(),
            'has_attachments' => true,
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function persistModel($entity)
    {
        /* @var TicketMessage $entity */
        $ticket = $entity->getTicket();

        $this->saveTicket($ticket);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteEntity($entity)
    {
        /* @var TicketMessage $entity */
        $ticket = $entity->getTicket();
        $ticket->messages->removeElement($entity);

        $this->saveTicket($ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @throws \Exception
     */
    protected function saveTicket(Ticket $ticket)
    {
        /** @var TicketManager $manager */
        $manager = $this->getContainer()->getTicketManager();
        $context = $manager->createAgentExecutorContext($this->getUser(), 'update', 'api');
        $manager->saveTicket($ticket, $context);
    }
}
