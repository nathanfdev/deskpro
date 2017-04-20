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

use Application\DeskPRO\Entity\Ticket as TicketEntity;
use Application\DeskPRO\Entity\TicketMessage;
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
     * @var int[]
     */
    private $ticketIds = [];

    /**
     * @var array
     */
    private $stars = [];

    /**
     * TicketHandler constructor.
     *
     * @param TicketLayoutFactory $layoutFactory
     * @param EntityManager       $em
     * @param FormFactory         $formFactory
     * @param FormErrorsGenerator $formErrorsGenerator
     */
    public function __construct(
        TicketLayoutFactory $layoutFactory,
        EntityManager       $em,
        FormFactory         $formFactory,
        FormErrorsGenerator $formErrorsGenerator
    ) {
        $this->layoutFactory       = $layoutFactory;
        $this->em                  = $em;
        $this->formFactory         = $formFactory;
        $this->formErrorsGenerator = $formErrorsGenerator;
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
        /** @var \Application\DeskPRO\EntityRepository\TicketMessage $repo */
        $repo    = $this->em->getRepository(TicketMessage::class);
        $message = $repo->getLastReply($entity, $context->getUser() && $context->getUser()->isAgent());

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
        if (!$this->stars && $context->getUser()) {
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
}
