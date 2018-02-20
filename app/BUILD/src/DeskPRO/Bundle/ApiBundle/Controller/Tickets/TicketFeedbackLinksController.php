<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\FeedbackSubscription;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketAwarePersistModelTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketFeedbackLink;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFeedbackLinkType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketFeedbackLinksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tickets/{parentId}/feedback_links")
 * @ApiDoc(target="all", section="Tickets", output="DeskPRO\Bundle\AppBundle\Entity\TicketFeedbackLink")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFeedbackLinkType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\TicketFeedbackLink",
 *          "ticket"="Application\DeskPRO\Entity\Ticket",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class TicketFeedbackLinksController extends AbstractTicketsCrudSubController
{
    use TicketSaveTrait;
    use TicketAwarePersistModelTrait { persistModel as protected traitPersistModel; }

    public static $entity         = TicketFeedbackLink::class;
    public static $type           = TicketFeedbackLinkType::class;
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

        $this->processSubscriptions(
            $entity,
            $form->get('is_subscribe_ticket_owner')->getData(),
            $form->get('is_subscribe_ticket_participants')->getData()
        );

        return $entity;
    }

    /**
     * @param TicketFeedbackLink $ticketFeedbackLink
     * @param bool               $isSubscribeTicketOwner
     * @param bool               $isSubscribeTicketParticipants
     */
    protected function processSubscriptions(
        TicketFeedbackLink $ticketFeedbackLink,
        $isSubscribeTicketOwner,
        $isSubscribeTicketParticipants)
    {
        // collect persons to subscribe
        $subscribePersons = [];
        if ($isSubscribeTicketOwner) {
            $subscribePersons[] = $ticketFeedbackLink->getTicket()->getPerson();
        }
        if ($isSubscribeTicketParticipants) {
            foreach ($ticketFeedbackLink->getTicket()->getParticipants() as $ticketParticipant) {
                $subscribePersons[] = $ticketParticipant->getTicket()->getPerson();
            }
        }

        // subscribe persons
        $feedback                = $ticketFeedbackLink->getFeedback();
        $portalPermissionManager = $this->get('portal_permissions_manager');
        $subscribedIds           = $this->getRepository(FeedbackSubscription::class)->getSubscribedPersonIds($feedback);

        foreach ($subscribePersons as $person) {
            if (in_array($person->getId(), $subscribedIds)) {
                continue;
            }

            $subscribedIds[] = $person->getId();

            // @TODO: optimize permission check for each user
            if ($portalPermissionManager
                    ->getPermissionsBagForPerson($person)
                    ->hasContentCategoryAccess($feedback)) {
                $feedbackSubscription = new FeedbackSubscription();
                $feedbackSubscription->setFeedback($feedback);
                $feedbackSubscription->setPerson($person);
                $this->getManager()->persist($feedbackSubscription);
            }
        }

        $this->getManager()->flush();
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketFeedbackLink $entity
     */
    protected function deleteEntity($entity)
    {
        $ticket = $entity->getTicket();
        $ticket->disableAutoTicketProcess();
        $ticket->removeFeedbackLink($entity);

        $this->saveTicket($ticket);
    }
}
