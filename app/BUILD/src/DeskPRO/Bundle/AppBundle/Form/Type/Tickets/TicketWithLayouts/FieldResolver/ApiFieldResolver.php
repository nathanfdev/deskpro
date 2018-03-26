<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomPerFieldType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments\ApiTicketMessageAttachmentCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDepartmentChoiceType;
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

        return new FormField(TicketDepartmentChoiceType::class, [
            'person'      => $context->getPerson(),
            'ticket'      => $context->getTicket(),
            'constraints' => [
                new Assert\NotNull(),
            ],
        ]);
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
            'owner'           => $context->getTicket(),
            'required'        => false,
            'agent_interface' => $context->isAgentView(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createPerson(TicketWithLayoutsContext $context)
    {
        $options = [
            'property_path' => 'person',
            'person'        => $context->getPerson(),
            'allow_create'  => true,
        ];

        if ($context->isFullLayout()) {
            $options['disabled'] = true;
        }

        return new FormField(PersonAssignType::class, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function createSubject(TicketWithLayoutsContext $context)
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
            'mapped'              => false,
            'person'              => $context->getPerson(),
            'ticket'              => $context->getTicket(),
            'ticket_message'      => $context->getMessage(),
            'data'                => $context->getMessage(),
            'format'              => '',
            'required'            => true,
            'message_constraints' => [
                new Assert\Length(['min' => 10]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createAttach(TicketWithLayoutsContext $context)
    {
        if (!$context->getMessage()) {
            return false;
        }

        return new FormField(ApiTicketMessageAttachmentCollectionType::class, [
            'required'       => false,
            'person'         => $context->getPerson(),
            'ticket_message' => $context->getMessage(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createCustomField(TicketWithLayoutsContext $context, $propertyPath, CustomDefAbstract $def = null)
    {
        if (!$this->canRenderCustomDef($def, $context)) {
            return false;
        }

        if ($def->getType() === CustomDefAbstract::TYPE_DISPLAY) {
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

    /**
     * {@inheritdoc}
     */
    protected function createContextualCustomPerField(TicketWithLayoutsContext $context, CustomFieldDefinition $def, $owner)
    {
        return new FormField(CustomPerFieldType::class, [
            'property_path'   => 'custom_per_data',
            'agent_interface' => $context->isAgentView(),
            'owner'           => $owner,
            'custom_def'      => $def,
            'inline'          => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubmittedPerson(TicketWithLayoutsContext $context)
    {
        $submitted = $context->getSubmittedData();
        if (isset($submitted[FormFields::PERSON])) {
            $personForm = clone $context->getForm()->get(FormFields::PERSON);
            $personForm->submit($submitted[FormFields::PERSON]);

            return $personForm->getData();
        }

        return $context->getForm()->get(FormFields::PERSON)->getData();
    }
}
