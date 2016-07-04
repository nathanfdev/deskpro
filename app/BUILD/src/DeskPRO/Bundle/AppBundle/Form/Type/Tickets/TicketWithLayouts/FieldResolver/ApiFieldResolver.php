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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDescriptionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants\TicketParticipantsType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApiFieldResolver.
 */
class ApiFieldResolver extends AbstractFieldResolver
{
    /**
     * {@inheritdoc}
     */
    protected function createDepartment(TicketWithLayoutsContext $context)
    {
        // we can just skip department field for api if it's single and already chosen
        if ($context->getTicket()->getDepartment() && $this->isNotSelectableDepartment($context)) {
            return false;
        }

        return parent::createDepartment($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function createCaptcha(TicketWithLayoutsContext $context)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function createCc(TicketWithLayoutsContext $context)
    {
        return new FormField(TicketParticipantsType::class, [
            'owner'    => $context->getTicket(),
            'is_agent' => false,
            'required' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createFollowers(TicketWithLayoutsContext $context)
    {
        return new FormField(TicketParticipantsType::class, [
            'owner'    => $context->getTicket(),
            'is_agent' => true,
            'required' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createPerson(TicketWithLayoutsContext $context)
    {
        return new FormField(PersonAssignType::class, [
            'property_path'    => 'person',
            'person'           => $context->getPerson(),
            'available_fields' => ['id', 'email', 'name'],
            'allow_create'     => true,
        ]);
    }

    /**
     * @return FormField
     */
    protected function createSubject()
    {
        return new FormField(TextType::class, [
            'constraints' => [
                new Assert\Length(['min' => 5]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createMessage(TicketWithLayoutsContext $context)
    {
        return new FormField(TicketDescriptionType::class, [
            'mapped'         => false,
            'person'         => $context->getPerson(),
            'ticket'         => $context->getTicket(),
            'ticket_message' => $context->getMessage(),
            'data'           => $context->getMessage(),
            'format'         => '',
            'required'       => true,
            'constraints'    => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 10]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createCustomField(TicketWithLayoutsContext $context, $propertyPath, CustomDefAbstract $def = null)
    {
        if (!$this->canRenderCustomDef($def)) {
            return false;
        }

        $options = [
            'custom_def'      => $def,
            'property_path'   => $propertyPath,
            'agent_interface' => $context->isAgentView(),
            'inline'          => true,
            'ticket'          => $context->getTicket(),
        ];

        return new FormField(CustomDataType::class, $options);
    }
}
