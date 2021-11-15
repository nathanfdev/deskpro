<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use Application\DeskPRO\TicketLayout\LayoutUtil;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDisableAutoProcessListener;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\FieldRendererInterface;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\AbstractFieldResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketWithLayoutsManipulatorType.
 */
class TicketWithLayoutsManipulatorType extends AbstractType
{
    /**
     * @var HierarchyGenerator
     */
    private $hierarchyGenerator;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * Constructor.
     *
     * @param HierarchyGenerator $hierarchyGenerator
     * @param FormFactory        $formFactory
     */
    public function __construct(HierarchyGenerator $hierarchyGenerator, FormFactory $formFactory)
    {
        $this->hierarchyGenerator = $hierarchyGenerator;
        $this->formFactory        = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 200);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('full_type_class')
            ->setAllowedTypes('full_type_class', 'string')
            ->setRequired(['field_resolver', 'field_renderer', 'layout_factory'])
            ->setAllowedTypes('field_resolver', AbstractFieldResolver::class)
            ->setAllowedTypes('field_renderer', FieldRendererInterface::class)
            ->setAllowedTypes('layout_factory', 'callable')
            ->setDefaults([
                'form_type' => null,

                // if user is not logged in then allow to create ticket
                // but don't modify user props as guest
                'ignore_user_fields' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TicketWithLayoutsType::class;
    }

    /**
     * Create the form based on ticket department.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        $context = TicketWithLayoutsContext::createOnPreSetData($event);
        TicketLayoutHelper::renderFormFields($context, function (LayoutField $field) use ($context) {
            return $field->getCriteria()->isTicketMatch($context->getTicket());
        });
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form    = $event->getForm();
        $data    = $form->getData();
        $context = TicketWithLayoutsContext::createOnPreSubmit($event);

        // fake person/org to process the full form
        $stubPerson = new Person();
        $stubPerson->setOrganization(new Organization());

        // fake ticket to process the full form
        $stubTicket = new Ticket();
        $stubTicket->disableAutoTicketProcess();
        $stubTicket->setPerson($stubPerson);
        if ($data) {
            $stubTicket->setBrand($data->getBrand());
            $stubTicket->setDepartment($data->getDepartment());
        }

        $options         = $form->getConfig()->getOptions();
        $fullFormOptions = [
            'person'              => $options['person'],
            'ticket_view_context' => $options['ticket_view_context'],
            'ticket_visibility'   => $options['ticket_visibility'],
        ];

        $fullForm = $this->formFactory->create($options['full_type_class'], $stubTicket, $fullFormOptions);
        $fullForm->submit($event->getData());

        // Pre set missed data for stub ticket
        $this->fillFullFormStubTicket($stubTicket, $event, $context->getActiveLayout());

        TicketLayoutHelper::renderFormFields($context, function (LayoutField $field) use ($stubTicket) {
            return $field->getCriteria()->isTicketMatch($stubTicket);
        });
    }

    /**
     * Layout fieldA might depend from other fieldB through criteria.
     * But fieldB might not be presented in layout directly.
     * So, Ticket form submitted data will not contain ticket/person/org data for fieldB, but we need those data
     * to properly process fieldA because it depends from fieldB through criteria.
     * We have to copy missed data manually to stub ticket
     *
     * @param FormEvent $mainFormEvent
     * @param Layout $mainFormLayout
     */
    private function fillFullFormStubTicket(Ticket $stubTicket, FormEvent $mainFormEvent, Layout $mainFormLayout)
    {
        $mainForm = $mainFormEvent->getForm();
        $options  = $mainForm->getConfig()->getOptions();
        $ticket   = $mainForm->getData();
        $person   = $options['person'];
        $org      = $person->getOrganization();

        $copyUserCustomFieldIds   = [];
        $copyOrgCustomFieldIds    = [];
        $copyTicketCustomFieldIds = [];

        $notInLayoutFields = LayoutUtil::getFieldsFromCriteriaNotInLayout($mainFormLayout);
        foreach ($notInLayoutFields as $f) {
            switch ($f->getFieldType()) {
                case FormFields::CATEGORY:
                    $stubTicket->setCategory($ticket->getCategory());

                    break;
                case FormFields::PRIORITY:
                    $stubTicket->setPriority($ticket->getPriority());

                    break;
                case FormFields::PRODUCT:
                    $stubTicket->setProduct($ticket->getProduct());

                    break;
                case FormFields::WORKFLOW:
                    $stubTicket->setWorkflow($ticket->getWorkflow());

                    break;
                case FormFields::USER_FIELD:
                    $copyUserCustomFieldIds[] = $f->getFieldId();

                    break;
                case FormFields::TICKET_FIELD:
                    $copyTicketCustomFieldIds[] = $f->getFieldId();

                    break;
                case FormFields::ORG_FIELD:
                    $copyOrgCustomFieldIds[] = $f->getFieldId();

                    break;
            }
        }

        if ($copyUserCustomFieldIds) {
            foreach ($person->getCustomData() as $customData) {
                if (in_array($customData->getField()->getId(), $copyUserCustomFieldIds)
                    || in_array($customData->getRootField()->getId(), $copyUserCustomFieldIds)
                ) {
                    $stubTicket->getPerson()->removeCustomDataForField($customData->getField());
                    $stubTicket->getPerson()->addCustomData(clone $customData);
                }
            }
        }

        if ($copyTicketCustomFieldIds) {
            foreach ($ticket->getCustomData() as $customData) {
                if (in_array($customData->getField()->getId(), $copyTicketCustomFieldIds)
                    || in_array($customData->getRootField()->getId(), $copyTicketCustomFieldIds)
                ) {
                    $stubTicket->removeCustomDataForField($customData->getField());
                    $stubTicket->addCustomData(clone $customData);
                }
            }
        }

        if ($copyOrgCustomFieldIds && $org) {
            foreach ($org->getCustomData() as $customData) {
                if (in_array($customData->getField()->getId(), $copyOrgCustomFieldIds)
                    || in_array($customData->getRootField()->getId(), $copyOrgCustomFieldIds)
                ) {
                    $stubTicket->getPerson()->getOrganization()->removeCustomDataForField($customData->getField());
                    $stubTicket->getPerson()->getOrganization()->addCustomData(clone $customData);
                }
            }
        }
    }

    /**
     * Update related ticket data after submission.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $ticket = $event->getForm()->getData();

        // update ticket message properties
        $ticketMessage = $ticket->messages->first();
        if ($ticketMessage instanceof TicketMessage) {
            $person = $ticket->getPerson();
            $ticketMessage->setPerson($person);

            foreach ($ticketMessage->getAttachments() as $attachment) {
                $attachment->setPerson($person);
            }
        }
    }
}
