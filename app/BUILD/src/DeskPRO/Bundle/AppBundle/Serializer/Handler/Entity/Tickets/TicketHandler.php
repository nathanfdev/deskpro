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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets;

use Application\DeskPRO\Entity\CustomDataTicket;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use Application\DeskPRO\Entity\TicketSla;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketExcerptDataService;
use DeskPRO\Bundle\AppBundle\Form\Error\FormErrorsGenerator;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsApiType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket as TicketModel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketCsv;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketLayout as TicketLayoutModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;

/**
 * Class TicketHandler.
 */
class TicketHandler extends AbstractEntityHandler
{
    /**
     * @var TicketLayoutFactory
     */
    private $layoutFactory;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var FormErrorsGenerator
     */
    private $formErrorsGenerator;

    /**
     * @var TicketExcerptDataService
     */
    private $excerptDataService;

    /**
     * @var int[]
     */
    private $ticketIds = [];

    /**
     * @var array
     */
    private $stars;

    /**
     * @var TicketMessage[]
     */
    private $excerpts;

    /**
     * @var array
     */
    private $labels;

    /**
     * @var array
     */
    private $customData;

    /**
     * @var array
     */
    private $ticketParticipants;

    /**
     * @var array
     */
    private $ticketSlas;

    /**
     * TicketHandler constructor.
     *
     * @param TicketLayoutFactory      $layoutFactory
     * @param EntityManager            $em
     * @param FormFactory              $formFactory
     * @param FormErrorsGenerator      $formErrorsGenerator
     * @param TicketExcerptDataService $excerptDataService
     */
    public function __construct(
        TicketLayoutFactory      $layoutFactory,
        EntityManager            $em,
        FormFactory              $formFactory,
        FormErrorsGenerator      $formErrorsGenerator,
        TicketExcerptDataService $excerptDataService
    ) {
        $this->layoutFactory       = $layoutFactory;
        $this->em                  = $em;
        $this->formFactory         = $formFactory;
        $this->formErrorsGenerator = $formErrorsGenerator;
        $this->excerptDataService  = $excerptDataService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return TicketEntity::class;
    }

    /**
     * @param TicketEntity $ticket
     *
     * @return array
     */
    public function getTicketLayout(TicketEntity $ticket)
    {
        $department = $ticket->getDepartment();
        $editLayout = $this->layoutFactory->getLayoutForTicketForm($department, true);
        $viewLayout = $this->layoutFactory->getLayoutForView($department);

        return [
            'edit' => [
                'user'  => new TicketLayoutModel($editLayout->getUserLayout(), 'user', $department),
                'agent' => new TicketLayoutModel($editLayout->getAgentLayout(), 'agent', $department),
            ],
            'view' => [
                'user'  => new TicketLayoutModel($viewLayout->getUserLayout(), 'user', $department),
                'agent' => new TicketLayoutModel($viewLayout->getAgentLayout(), 'agent', $department),
            ],
        ];
    }

    /**
     * @param TicketEntity                 $entity
     * @param SideloadSerializationContext $context
     *
     * @return array
     */
    public function getExcerpt(TicketEntity $entity, SideloadSerializationContext $context)
    {
        if (null === $this->excerpts) {
            $this->excerpts = $this->excerptDataService->getTicketsLastReply(
                $this->ticketIds,
                $context->getUser() && $context->getUser()->isAgent()
            );
        }

        $message = isset($this->excerpts[$entity->getId()]) ? $this->excerpts[$entity->getId()] : null;
        if (!$message) {
            return;
        }

        $excerpt = $message->getMessagePreviewText(200);

        return [
            'message_id' => $message->getId(),
            'excerpt'    => $excerpt,
        ];
    }

    /**
     * @param TicketEntity                 $entity
     * @param SideloadSerializationContext $context
     * @param string                       $viewContext
     *
     * @return array
     */
    public function getTicketErrors(TicketEntity $entity, SideloadSerializationContext $context, $viewContext)
    {
        $form = $this->formFactory->create(TicketWithLayoutsApiType::class, $entity, [
            'ticket_view_context' => $viewContext,
            'ticket_visibility'   => TicketWithLayoutsContext::VISIBILITY_EDIT,
            'person'              => $context->getUser(),
            'disabled'            => true,
        ]);

        FormValidatorChecker::submitForm($form);

        return $this->formErrorsGenerator->generateFormErrors($form);
    }

    /**
     * @param TicketEntity                 $entity
     * @param SideloadSerializationContext $context
     *
     * @return string|null
     */
    public function getStar(TicketEntity $entity, SideloadSerializationContext $context)
    {
        if (null === $this->stars && $context->getUser()) {
            /** @var \Application\DeskPRO\DBAL\Connection $connection */
            $connection  = $this->em->getConnection();
            $this->stars = $connection->fetchAllKeyValue(
                'SELECT f.ticket_id, f.color FROM  tickets_flagged AS f WHERE f.ticket_id IN (?) AND f.person_id = ?',
                [implode(',', $this->ticketIds), $context->getUser()->getId()]
            );
        }

        return isset($this->stars[$entity->getId()]) ? $this->stars[$entity->getId()] : null;
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketEntity $entity
     */
    protected function createModel($entity, SideloadSerializationContext $context)
    {
        $this->ticketIds[] = $entity->getId();

        $serializerClass = $context->getMappedClass(TicketEntity::class);
        if ($serializerClass === TicketCsv::class) {
            return new TicketCsv($entity);
        }

        $model = new TicketModel($entity);
        $model->setStar(new CallbackDeferredProperty([$this, 'getStar'], [$entity, $context]));
        $model->setLabels(new CallbackDeferredProperty([$this, 'getLabels'], [$entity]));
        $model->setCustomData(new CallbackDeferredProperty([$this, 'getCustomData'], [$entity]));
        $model->setCc(new CallbackDeferredProperty([$this, 'getTicketParticipants'], [$entity]));
        $model->setTicketSlas(new CallbackDeferredProperty([$this, 'getTicketSlas'], [$entity]));

        $sideloads = $context->getSideloadStore();
        $sideloads->addCustomSideload(
            'ticket_layout',
            $entity->getId(),
            new CallbackDeferredProperty([$this, 'getTicketLayout'], [$entity]),
            $model
        );
        $sideloads->addCustomSideload(
            'ticket_excerpt',
            $entity->getId(),
            new CallbackDeferredProperty([$this, 'getExcerpt'], [$entity, $context]),
            $model
        );

        // validation
        $sideloads->addCustomSideload(
            'ticket_agent_errors',
            $entity->getId(),
            new CallbackDeferredProperty(
                [$this, 'getTicketErrors'],
                [$entity, $context, TicketWithLayoutsContext::VIEW_AGENT]
            ),
            $model
        );
        $sideloads->addCustomSideload(
            'ticket_user_errors',
            $entity->getId(),
            new CallbackDeferredProperty(
                [$this, 'getTicketErrors'],
                [$entity, $context, TicketWithLayoutsContext::VIEW_USER]
            ),
            $model
        );

        return $model;
    }

    /**
     * @param TicketEntity $entity
     *
     * @return LabelTicket[]
     */
    public function getLabels(TicketEntity $entity)
    {
        if (null === $this->labels) {
            $result = $this->em->getRepository(LabelTicket::class)->findBy([
                'ticket' => $this->ticketIds,
            ]);

            $this->labels = [];
            foreach ($result as $label) {
                $this->labels[$label->getTicket()->getId()][] = $label;
            }
        }

        if (isset($this->labels[$entity->getId()])) {
            return $this->labels[$entity->getId()];
        }

        return;
    }

    /**
     * @param TicketEntity $entity
     *
     * @return CustomDataTicket[]
     */
    public function getCustomData(TicketEntity $entity)
    {
        if (null === $this->customData) {
            $result = $this->em->getRepository(CustomDataTicket::class)->findBy([
                'ticket' => $this->ticketIds,
            ]);

            $this->customData = [];
            foreach ($result as $value) {
                $this->customData[$value->getTicketId()][] = $value;
            }
        }

        if (isset($this->customData[$entity->getId()])) {
            return new ArrayCollection($this->customData[$entity->getId()]);
        }

        return new ArrayCollection([]);
    }

    /**
     * @param TicketEntity $entity
     *
     * @return TicketParticipant[]
     */
    public function getTicketParticipants(TicketEntity $entity)
    {
        if (null === $this->ticketParticipants) {
            $result = $this->em->getRepository(TicketParticipant::class)->findBy([
                'ticket' => $this->ticketIds,
            ]);

            $this->ticketParticipants = [];
            foreach ($result as $value) {
                $this->ticketParticipants[$value->getTicket()->getId()][] = $value;
            }
        }

        if (isset($this->ticketParticipants[$entity->getId()])) {
            $participants = new ArrayCollection($this->ticketParticipants[$entity->getId()]);

            return $participants->map(function (TicketParticipant $participant) {
                return $participant->getPerson();
            });
        }

        return new ArrayCollection([]);
    }

    /**
     * @param TicketEntity $entity
     *
     * @return TicketSla[]
     */
    public function getTicketSlas(TicketEntity $entity)
    {
        if (null === $this->ticketSlas) {
            $result = $this->em->getRepository(TicketSla::class)->findBy([
                'ticket' => $this->ticketIds,
            ]);

            $this->ticketSlas = [];
            foreach ($result as $value) {
                $this->ticketSlas[$value->getTicket()->getId()][] = $value;
            }
        }

        if (isset($this->ticketSlas[$entity->getId()])) {
            return new ArrayCollection($this->ticketSlas[$entity->getId()]);
        }

        return new ArrayCollection([]);
    }
}
