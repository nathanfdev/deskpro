<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketAwarePersistModelTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketCommunityTopicLink;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketCommunityTopicLinkType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketCommunityTopicLinksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tickets/{parentId}/community_topic_links")
 * @ApiDoc(target="all", section="Tickets", output="DeskPRO\Bundle\AppBundle\Entity\TicketCommunityTopicLink")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketCommunityTopicLinkType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\TicketCommunityTopicLink",
 *          "ticket"="Application\DeskPRO\Entity\Ticket",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class TicketCommunityTopicLinksController extends AbstractTicketsCrudSubController
{
    use TicketSaveTrait;
    use TicketAwarePersistModelTrait { persistModel as protected traitPersistModel; }

    public static $entity         = TicketCommunityTopicLink::class;
    public static $type           = TicketCommunityTopicLinkType::class;
    public static $parentProperty = 'ticket';
    public static $listSort       = 'id';
    public static $listOrder      = 'asc';
    public static $exposeOnly     = ['get', 'list', 'post', 'csv', 'count', 'delete'];
    public static $sortOptions    = [
        'date_created' => 'date_created',
        'date'         => 'date_created', // alias
    ];

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'ticket' => $this->findParentOr404(),
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function persistModel($entity, FormInterface $form = null)
    {
        $this->traitPersistModel($entity, $form);

        // @TODO: do we need transaction here (don't create link if subscription fail)?
        /* @var TicketCommunityTopicLink $entity */
        $this->get('community_subscription_helper')->subscribeTicketPersons(
            $entity->getTopic(),
            $entity->getTicket(),
            $form->get('is_subscribe_ticket_owner')->getData(),
            $form->get('is_subscribe_ticket_participants')->getData()
        );

        return $entity;
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketCommunityTopicLink $entity
     */
    protected function deleteEntity($entity)
    {
        $ticket = $entity->getTicket();
        $ticket->disableAutoTicketProcess();
        $ticket->removeTopicLink($entity);

        $this->saveTicket($ticket);
    }
}
