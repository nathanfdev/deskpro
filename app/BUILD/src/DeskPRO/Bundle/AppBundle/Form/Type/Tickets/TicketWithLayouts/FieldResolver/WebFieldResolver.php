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
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomPerFieldType;
use DeskPRO\Bundle\AppBundle\Form\Type\HiddenEntityType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailChoiceType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDescriptionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants\TicketParticipantsWebType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\DpCaptchaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WebFieldResolver.
 */
class WebFieldResolver extends AbstractFieldResolver
{
    /**
     * {@inheritdoc}
     */
    protected function createDepartment(TicketWithLayoutsContext $context)
    {
        // if we have just one department or the 'hide_department_field' option is set
        // then render hidden field instead of selectbox

        $department = $context->getTicket()->getDepartment();
        if ($department && ($this->isNotSelectableDepartment($context) || $context->getOption('hide_department_field'))) {
            return new FormField(HiddenEntityType::class, [
                'entity_class' => Department::class,
            ]);
        }

        return parent::createDepartment($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function createCaptcha(TicketWithLayoutsContext $context)
    {
        if (!$context->getOption('use_captcha')) {
            return false;
        }

        // NOTE: you may want to view TicketLayoutFactory.
        // In TicketLayoutFactory we can, at times, add a CAPTCHA to the ticket
        // layout under certain circumstances (when anti-abuse is violated, for example).

        $options = [
            'mapped'         => false,
            'error_bubbling' => false,
        ];

        return new FormField(DpCaptchaType::class, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function createCc(TicketWithLayoutsContext $context)
    {
        return new FormField(TicketParticipantsWebType::class, [
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
        return new FormField(CombinedType::class, [
            'forms' => [
                $this->createUserNameOptions($context),
                $this->createUserEmailOptions($context),
            ],
        ]);
    }

    /**
     * @return FormField
     */
    protected function createSubject()
    {
        return new FormField(TextType::class, [
            'label'       => $this->phrase('portal.forms.label_subject'),
            'required'    => true,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 5]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createMessage(TicketWithLayoutsContext $context)
    {
        if (TicketWithLayoutsContext::VISIBILITY_NEW !== $context->getVisibility()) {
            return false;
        }

        return new FormField(TicketDescriptionType::class, [
            'mapped'         => false,
            'label'          => false,
            'person'         => $context->getPerson(),
            'ticket'         => $context->getTicket(),
            'ticket_message' => $context->getMessage(),
            'data'           => $context->getMessage(),
            'format'         => 'html',
            'required'       => true,
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

        $options = [
            'custom_def'      => $def,
            'property_path'   => $propertyPath,
            'agent_interface' => $context->isAgentView(),
            'inline'          => false,
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
            'label'           => $def->getTitle(),
            'owner'           => $owner,
            'custom_def'      => $def,
            'inline'          => false,
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return array
     */
    private function createUserNameOptions(TicketWithLayoutsContext $context)
    {
        return [
            'name'    => FormFields::USER_NAME,
            'type'    => TextType::class,
            'options' => [
                'property_path' => 'person.name',
                'label'         => $this->phrase('portal.forms.label_name'),
                'empty_data'    => $context->getPerson()->getDisplayName(false),
                'constraints'   => [
                    new Assert\NotBlank(),
                ],
            ],
        ];
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return array
     */
    private function createUserEmailOptions(TicketWithLayoutsContext $context)
    {
        $person = $context->getPerson();
        if ($person->isUser()) {
            return [
                'name'    => FormFields::USER_EMAIL,
                'type'    => PersonEmailChoiceType::class,
                'options' => [
                    'property_path' => 'ticket_person_email',
                    'label'         => $this->phrase('portal.forms.label_email'),
                    'person'        => $person,
                ],
            ];
        }

        return [
            'name'    => FormFields::USER_EMAIL,
            'type'    => PersonEmailType::class,
            'options' => [
                'property_path' => 'person.primary_email',
                'label'         => $this->phrase('portal.forms.label_email'),

                // ignore the "unique entity" constraint here
                'constraints' => [],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubmittedPerson(TicketWithLayoutsContext $context)
    {
        return $context->getTicket()->getPerson();
    }
}
