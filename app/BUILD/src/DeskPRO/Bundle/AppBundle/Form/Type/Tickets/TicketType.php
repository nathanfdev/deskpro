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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketWorkflow;
use DeskPRO\Bundle\AppBundle\Form\BrandFormHelper;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants\TicketParticipantsType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;
use DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer\CustomFieldAliasTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketType.
 */
class TicketType extends AbstractType
{
    /**
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * @var BrandFormHelper
     */
    private $helper;

    /**
     * Constructor.
     *
     * @param CustomFieldManager $fieldManager
     * @param BrandFormHelper    $helper
     */
    public function __construct(CustomFieldManager $fieldManager, BrandFormHelper $helper)
    {
        $this->fieldManager = $fieldManager;
        $this->helper       = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'empty_data' => '(No Subject)',
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'data'  => $this->getDefaultDepartment($builder),
            ])
            ->add('parent', EntityType::class, [
                'class'         => Ticket::class,
                'property_path' => 'parent_ticket',
            ])
            ->add('language', EntityType::class, [
                'class' => Language::class,
            ])
            ->add('category', EntityType::class, [
                'class' => TicketCategory::class,
            ])
            ->add('priority', EntityType::class, [
                'class' => TicketPriority::class,
            ])
            ->add('workflow', EntityType::class, [
                'class' => TicketWorkflow::class,
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
            ])
            ->add('person', PersonAssignType::class, [
                'person' => $options['person'],
            ])
            ->add('agent', PersonAssignType::class)
            ->add('agent_team', EntityType::class, [
                'class' => AgentTeam::class,
            ])
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
            ])
            ->add('status', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => Ticket::getTicketStatuses(),
            ])
            ->add('is_hold', ApiBooleanType::class)
            ->add('urgency', NumberType::class)
            ->add('labels', LabelsCollectionType::class, [
                'labels_class'   => LabelTicket::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'ticket',
            ])
            ->add('cc', TicketParticipantsType::class, [
                'owner'           => $builder->getData(),
                'agent_interface' => $options['agent_interface'],
            ])
            ->add('fields', CombinedType::class, [
                'forms'          => $this->getCustomDataFields($builder, $options),
                'error_bubbling' => false,
            ])
            ->add('star', TicketStarType::class, [
                'mapped' => false,
                'ticket' => $builder->getData(),
                'person' => $options['person'],
                'inline' => true,
            ])
            ->add('suppress_user_notify', ApiBooleanType::class, [
                'mapped' => false,
            ])
        ;

        // resolve field name aliases
        $fieldNameResolver = $this->fieldManager->getFieldNameResolver(CustomDefTicket::class);
        $builder->addEventSubscriber($fieldNameResolver);

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onAddMessageField']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefaults([
                'data_class'      => Ticket::class,
                'agent_interface' => false,
            ])
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * Ticket message is optional so decide to add this field on form submission.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onAddMessageField(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        /** @var Ticket $ticket */
        $ticket = $form->getData();

        if (isset($data['message'])) {
            $message = $ticket->getMessages()->first();
            if (!$message) {
                $message = new TicketMessage();
                $ticket->addMessage($message);
            }

            $form->add('message', TicketMessageType::class, [
                'mapped'         => false,
                'ticket'         => $form->getData(),
                'person'         => $form->getConfig()->getOption('person'),
                'ticket_message' => $message,
            ]);
        }

        if (isset($data['messages'])) {
            $form->add('messages', CollectionType::class, [
                'mapped'        => false,
                'allow_add'     => true,
                'entry_type'    => TicketMessageType::class,
                'entry_options' => [
                    'ticket' => $form->getData(),
                    'person' => $form->getConfig()->getOption('person'),
                ],
            ]);
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return array
     */
    private function getCustomDataFields(FormBuilderInterface $builder, array $options)
    {
        $defs   = $this->fieldManager->getAvailableTicketDefs();
        $fields = [];

        foreach ($defs as $def) {
            $fields[] = [
                'name'    => $def->getId(),
                'type'    => CustomDataType::class,
                'options' => [
                    'custom_def'      => $def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => $options['agent_interface'],
                    'inline'          => true,
                    'ticket'          => $builder->getData(),
                ],
            ];
        }

        return $fields;
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @return Department|null
     */
    private function getDefaultDepartment(FormBuilderInterface $builder)
    {
        $type = $builder->getOption('agent_interface')
            ? DefaultDepartmentSettings::DEFAULT_DEPARTMENT_AGENT_TYPE
            : DefaultDepartmentSettings::DEFAULT_DEPARTMENT_USER_TYPE;

        return $this->helper->getDefaultDepartment($type);
    }
}
