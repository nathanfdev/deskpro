<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
        $options = [
            'person'      => $context->getPerson(),
            'ticket'      => $context->getTicket(),
            'brand'       => $this->getSubmittedBrand($context),
            'constraints' => [
                new Assert\NotNull(),
            ],
        ];

        // we can just skip department field for api if it's single and already chosen
        if ($context->getTicket()->getDepartment() && $this->isNotSelectableDepartment($context, $this->getSubmittedBrand($context))) {
            $options['empty_data'] = (string) $context->getTicket()->getDepartment()->getId();
        }

        return new FormField(TicketDepartmentChoiceType::class, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function createBrand()
    {
        $options = [
            'class'    => Brand::class,
            'required' => false,
        ];

        $activeBrand = $this->brandStack->getActive();
        if ($activeBrand) {
            $options['empty_data'] = (string) $this->brandStack->getActive()->getBrand()->getId();
        }

        return new FormField(EntityType::class, $options);
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

        if ($context->ignoreUserFields() && ($def instanceof CustomDefPerson || $def instanceof CustomDefOrganization)) {
            $options['mapped'] = false;
        }

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
        return $this->getSubmittedField($context, FormFields::PERSON);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return mixed
     */
    protected function getSubmittedBrand(TicketWithLayoutsContext $context)
    {
        return $this->getSubmittedField($context, FormFields::BRAND);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubmittedField(TicketWithLayoutsContext $context, $field)
    {
        if (!$context->getForm()->has($field)) {
            return;
        }

        $submitted = $context->getSubmittedData();
        if (isset($submitted[$field])) {
            $childForm = clone $context->getForm()->get($field);
            $childForm->submit($submitted[$field]);

            return $childForm->getData();
        }

        return $context->getForm()->get($field)->getData();
    }
}
