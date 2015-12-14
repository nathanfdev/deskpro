<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;
use DeskPRO\Bundle\AppBundle\CustomFields\CustomDataCollection;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformerRequest;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Doctrine\ORM\EntityManager;

/**
 * Class TicketTransformer.
 */
class TicketTransformer extends AbstractDataSerializerTransformer
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var TicketLayoutFactory
     */
    private $ticket_layout_factory;

    /**
     * TicketTransformer constructor.
     *
     * @param $em
     */
    public function __construct(EntityManager $em, TicketLayoutFactory $ticket_layout_factory)
    {
        $this->em                    = $em;
        $this->ticket_layout_factory = $ticket_layout_factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'id',
            'ref',
            'auth',
            'parent_ticket',
            'language',
            'department',
            'category',
            'priority',
            'workflow',
            'product',
            'person',
            'person_email',
            'agent',
            'agent_team',
            'organization',
            'linked_chat',
            // 'sent_to_address',
            'email_account',
            'email_account_address',
            'creation_system',
            'creation_system_option',
            'ticket_hash',
            'status',
            'hidden_status',
            'is_hold',
            'urgency',
            'feedback_rating',
            'date_feedback_rating',
            'date_created',
            'date_resolved',
            'date_archived',
            'date_first_agent_assign',
            'date_first_agent_reply',
            'date_last_agent_reply',
            'date_last_user_reply',
            'date_agent_waiting',
            'date_user_waiting',
            'date_status',
            'total_user_waiting',
            'total_to_first_reply',
            'locked_by_agent',
            'date_locked',
            'has_attachments',
            'subject',
            'original_subject',
            'properties',
            'problems',
            'count_agent_replies',
            'count_user_replies',
            'worst_sla_status',
            'waiting_times',
            'ticket_slas',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $ticket = $transformation_request->getDataToBeTransformed();

        $props = [
            'sent_to_address' => $ticket->getSentToAddresses(),
        ];

        $includes = $transformation_request->getSerializerContext()->getRequestedIncludes();
        if (in_array('ticket_excerpt', $includes)) {
            // TODO make this more efficient

            /** @var \Application\DeskPRO\Entity\TicketMessage $message */
            $message = $this->em->getRepository('DeskPRO:TicketMessage')->getLastReply($ticket);

            if ($message && $excerpt = $message->getMessagePreviewText(200)) {
                $transformation_request->getSerializerContext()->getSideloads()->addSideloadDataId('ticket_excerpt', $ticket->id, new PrimitiveArray([
                    'message_id' => $message->getId(),
                    'excerpt'    => $excerpt,
                ]));

            // TODO this is for mobile testing
            } else {
                $excerpt = preg_replace('#[^a-zA-Z0-9\' \.]#', '', \Faker\Factory::create()->realText());
                $excerpt = preg_replace('#-{2}#', '-', $excerpt);
                $transformation_request->getSerializerContext()->getSideloads()->addSideloadDataId('ticket_excerpt', $ticket->id, new PrimitiveArray([
                    'message_id' => $ticket->id + 1000,
                    'excerpt'    => $excerpt,
                ]));
            }
        }

        if (in_array('ticket_layout', $includes)) {
            // TODO make this more efficient
            $edit_layout = $this->ticket_layout_factory->getLayoutForTicketForm($ticket->department);
            $view_layout = $this->ticket_layout_factory->getLayoutForView($ticket->department);

            $transformation_request->getSerializerContext()->getSideloads()->addSideloadDataId('ticket_layout', $ticket->id, new PrimitiveArray([
                'edit' => [
                    'user'  => $edit_layout->getUserLayout()->exportToArray(),
                    'agent' => $edit_layout->getAgentLayout()->exportToArray(),
                ],
                'view' => [
                    'user'  => $view_layout->getUserLayout()->exportToArray(),
                    'agent' => $view_layout->getAgentLayout()->exportToArray(),
                ],
            ]));
        }

        if ($ticket->person_email) {
            $props['person_email'] = $ticket->person_email->getEmail();
        } elseif ($ticket->getPerson() && $ticket->getPerson()->getPrimaryEmail()) {
            $props['person_email'] = $ticket->person->getPrimaryEmail()->getEmail();
        } else {
            $props['person_email'] = null;
        }

        $props['fields']       = new CustomDataCollection($ticket->getCustomData());
        $props['labels']       = $ticket->getLabelsArray();
        $props['participants'] = $this->selectIds($ticket->getUserParticipants());
        $props['followers']    = $this->selectIds($ticket->getAgentParticipants());

        // add participant and followers when person is side loaded
        $context   = $transformation_request->getSerializerContext();
        $className = (new \ReflectionClass(Person::class))->getShortName();
        $type      = strtolower($className);
        if ($context->isTypeIncluded($type)) {
            $context->getSideloads()->addSideloadCollection($type, $ticket->getUserParticipants());
            $context->getSideloads()->addSideloadCollection($type, $ticket->getAgentParticipants());
        }

        // child and sibling tickets
        $repository             = $this->em->getRepository(Ticket::class);
        $children               = $repository->findBy(['parent_ticket' => $ticket]);
        $props['child_tickets'] = $this->selectIds($children);
        $siblings               = $ticket->getParentTicket()
                         ? $repository->findBy(['parent_ticket' => $ticket->getParentTicket()])
                         : [];
        $siblings = array_filter($siblings, function ($sibling) use ($ticket) { return $sibling !== $ticket; });
        $props['sibling_tickets'] = $this->selectIds($siblings);

        // add child and sibling tickets when ticket is side loaded
        $className = (new \ReflectionClass(Ticket::class))->getShortName();
        $type      = strtolower($className);
        if ($context->isTypeIncluded($type)) {
            $context->getSideloads()->addSideloadCollection($type, $children);
            $context->getSideloads()->addSideloadCollection($type, $siblings);
        }

        return $props;
    }

    private function selectIds($collection)
    {
        $ids = [];
        foreach ($collection as $element) {
            $ids[] = $element->getId();
        }

        return array_values(array_unique($ids));
    }
}
