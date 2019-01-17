<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomPerFieldType;
use DeskPRO\Bundle\AppBundle\Form\Type\HiddenEntityType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailChoiceType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments\WebTicketMessageAttachmentCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDepartmentChoiceType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDescriptionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants\TicketParticipantsWebType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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

        return new FormField(TicketDepartmentChoiceType::class, [
            'label'       => $this->phrase('portal.forms.label_department'),
            'person'      => $context->getPerson(),
            'ticket'      => $context->getTicket(),
            'placeholder' => '',
            'constraints' => [
                new Assert\NotNull(),
                new AppAssert\LeafDepartment(),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createBrand()
    {
        return false;
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
        return new FormField(DpCaptchaType::class, [
            'mapped'         => false,
            'error_bubbling' => false,
        ]);
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
     * {@inheritdoc}
     */
    protected function createSubject(TicketWithLayoutsContext $context)
    {
        $subjectType = $context->getOption('subject_type');
        if (in_array($subjectType, ['default', 'message'], true)) {
            $params = [];
            if ($subjectType === 'default') {
                $params['data'] = $context->getOption('default_subject');
            }

            return new FormField(HiddenType::class, $params);
        }

        return new FormField(TextType::class, [
            'label' => $context->isWidgetType()
                ? $this->phrase('portal.widget.label_subject')
                : $this->phrase('portal.forms.label_subject'),
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
            'message_label'  => $context->isWidgetType()
                ? $this->phrase('portal.widget.label_message')
                : $this->phrase('portal.forms.label_message'),
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

        return new FormField(WebTicketMessageAttachmentCollectionType::class, [
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

        $options = [
            'custom_def'      => $def,
            'property_path'   => $propertyPath,
            'agent_interface' => $context->isAgentView(),
            'inline'          => false,
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
        $options = [
            'property_path' => 'person.name',
            'label'         => $context->isWidgetType()
                ? $this->phrase('portal.widget.label_name')
                : $this->phrase('portal.forms.label_name'),
            'empty_data'  => $context->getPerson()->getDisplayName(false),
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ];

        if ($context->isFullLayout()) {
            $options['disabled'] = true;
        }
        if ($context->ignoreUserFields()) {
            $options['mapped'] = false;
        }

        return [
            'name'    => FormFields::USER_NAME,
            'type'    => TextType::class,
            'options' => $options,
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
            $options = [
                'person'        => $person,
                'property_path' => 'ticket_person_email',
                'label'         => $context->isWidgetType()
                    ? $this->phrase('portal.widget.label_email')
                    : $this->phrase('portal.forms.label_email'),
            ];

            if ($context->isFullLayout()) {
                $options['disabled'] = true;
            }

            return [
                'name'    => FormFields::USER_EMAIL,
                'type'    => PersonEmailChoiceType::class,
                'options' => $options,
            ];
        } else {
            $options = [
                'property_path' => 'person.primary_email',
                'label'         => $context->isWidgetType()
                    ? $this->phrase('portal.widget.label_email')
                    : $this->phrase('portal.forms.label_email'),

                // ignore the "unique entity" constraint here
                'constraints' => [
                    new AppAssert\Person\Email\NotSystemEmail(),
                ],
            ];

            if ($context->isFullLayout()) {
                $options['disabled'] = true;
            }

            return [
                'name'    => FormFields::USER_EMAIL,
                'type'    => PersonEmailType::class,
                'options' => $options,
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubmittedPerson(TicketWithLayoutsContext $context)
    {
        return $context->getTicket()->getPerson();
    }
}
