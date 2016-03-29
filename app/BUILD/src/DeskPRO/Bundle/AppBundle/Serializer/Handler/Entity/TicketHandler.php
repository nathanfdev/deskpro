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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Ticket as TicketEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket as TicketModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLinker;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class TicketHandler.
 */
class TicketHandler extends AbstractEntityHandler
{
    /**
     * @var TicketLinker
     */
    private $ticketLinker;

    /**
     * @var TicketLayoutFactory
     */
    private $layoutFactory;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * TicketHandler constructor.
     *
     * @param TicketLinker        $ticketLinker
     * @param TicketLayoutFactory $layoutFactory
     * @param EntityManager       $em
     */
    public function __construct(TicketLinker $ticketLinker, TicketLayoutFactory $layoutFactory, EntityManager $em)
    {
        $this->ticketLinker  = $ticketLinker;
        $this->layoutFactory = $layoutFactory;
        $this->em            = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return TicketEntity::class;
    }

    /**
     * @param JsonSerializationVisitor     $visitor
     * @param TicketEntity                 $entity
     * @param array                        $type
     * @param SideloadSerializationContext $context
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $entity, $type, SideloadSerializationContext $context)
    {
        $sideloads = $context->getSideloadStore();
        $sideloads->addCustomSideload(
            'ticket_layout',
            $entity->getId(),
            new CallbackDeferredProperty([$this, 'getTicketLayout'], [$entity])
        );
        $sideloads->addCustomSideload(
            'ticket_excerpt',
            $entity->getId(),
            new CallbackDeferredProperty([$this, 'getExcerpt'], [$entity])
        );

        return parent::serialize($visitor, $entity, $type, $context);
    }

    /**
     * @param TicketEntity $ticket
     *
     * @return array
     */
    public function getTicketLayout(TicketEntity $ticket)
    {
        $edit_layout = $this->layoutFactory->getLayoutForTicketForm($ticket->getDepartment());
        $view_layout = $this->layoutFactory->getLayoutForView($ticket->getDepartment());

        return [
            'edit' => [
                'user'  => $edit_layout->getUserLayout()->exportToArray(),
                'agent' => $edit_layout->getAgentLayout()->exportToArray(),
            ],
            'view' => [
                'user'  => $view_layout->getUserLayout()->exportToArray(),
                'agent' => $view_layout->getAgentLayout()->exportToArray(),
            ],
        ];
    }

    /**
     * @param TicketEntity $entity
     *
     * @return array
     */
    public function getExcerpt(TicketEntity $entity)
    {
        /** @var \Application\DeskPRO\EntityRepository\TicketMessage $repo */
        $repo = $this->em->getRepository('DeskPRO:TicketMessage');

        /** @var \Application\DeskPRO\Entity\TicketMessage $message */
        $message = $repo->getLastReply($entity);

        if ($message && $excerpt = $message->getMessagePreviewText(200)) {
            return [
                'message_id' => $message->getId(),
                'excerpt'    => $excerpt,
            ];
        } else {
            $excerpt = preg_replace('#[^a-zA-Z0-9\' \.]#', '', \Faker\Factory::create()->realText());
            $excerpt = preg_replace('#-{2}#', '-', $excerpt);

            return [
                'message_id' => $entity->getId() + 1000,
                'excerpt'    => $excerpt,
            ];
        }
    }

    /**
     * @param TicketEntity $entity
     *
     * @return TicketModel
     */
    protected function createModel($entity)
    {
        return new TicketModel(
            $entity,
            array_values($this->ticketLinker->getTicketChildren($entity)),
            array_values($this->ticketLinker->getTicketSiblings($entity))
        );
    }
}
