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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketWorkflow;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants\TicketParticipantsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TicketType.
 */
class TicketType extends AbstractType
{
    /**
     * @var CustomFieldManager
     */
    private $field_manager;

    /**
     * Constructor.
     *
     * @param CustomFieldManager $field_manager
     */
    public function __construct(CustomFieldManager $field_manager)
    {
        $this->field_manager = $field_manager;
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
            ->add('person', EntityType::class, [
                'class' => Person::class,
            ])
            ->add('agent', EntityType::class, [
                'class' => Person::class,
            ])
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
                'owner'    => $builder->getData(),
                'is_agent' => false,
                'required' => false,
            ])
            ->add('followers', TicketParticipantsType::class, [
                'owner'    => $builder->getData(),
                'is_agent' => true,
                'required' => false,
            ])
            ->add('fields', CombinedType::class, [
                'forms'          => $this->getCustomDataFields($builder, $options),
                'error_bubbling' => false,
            ])
        ;

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'      => Ticket::class,
                'agent_interface' => false,
            ])
        ;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return array
     */
    private function getCustomDataFields(FormBuilderInterface $builder, array $options)
    {
        $field_defs  = $this->field_manager->getAvailableTicketDefs();
        $form_fields = [];

        foreach ($field_defs as $field_def) {
            $form_fields[] = [
                'name'    => $field_def->getId(),
                'type'    => CustomDataType::class,
                'options' => [
                    'custom_def'      => $field_def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => $options['agent_interface'],
                    'label'           => $field_def->getTitle(),
                    'inline'          => true,
                    'ticket'          => $builder->getData(),
                ],
            ];
        }

        return $form_fields;
    }
}
