<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions\Tickets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketWorkflow;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\BaseMassActionParamsType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDisableAutoProcessListener;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants\TicketParticipantsType;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketMassActionParamsType.
 */
class TicketMassActionParamsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('set_workflow', EntityType::class, [
                'class'         => TicketWorkflow::class,
                'property_path' => 'workflow',
            ])
            ->add('set_product', EntityType::class, [
                'class'         => Product::class,
                'property_path' => 'product',
            ])
            ->add('set_language', EntityType::class, [
                'class'         => Language::class,
                'property_path' => 'language',
            ])
            ->add('set_category', EntityType::class, [
                'class'         => TicketCategory::class,
                'property_path' => 'category',
            ])
            ->add('set_priority', EntityType::class, [
                'class'         => TicketPriority::class,
                'property_path' => 'priority',
            ])
            ->add('set_status', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => Ticket::getTicketStatuses(),
                'property_path'     => 'status',
            ])
            ->add('assign', CombinedType::class, [
                'error_bubbling' => false,
                'forms'          => [
                    [
                        'name'    => 'agent',
                        'type'    => EntityType::class,
                        'options' => [
                            'class'         => Person::class,
                            'property_path' => 'agent',
                        ],
                    ],
                    [
                        'name'    => 'team',
                        'type'    => EntityType::class,
                        'options' => [
                            'class'         => AgentTeam::class,
                            'property_path' => 'agent_team',
                        ],
                    ],
                    [
                        'name'    => 'department',
                        'type'    => EntityType::class,
                        'options' => [
                            'class'         => Department::class,
                            'property_path' => 'department',
                        ],
                    ],
                ],
            ])
            ->add('reply', TicketMassActionReplyType::class, [
                'mapped'      => false,
                'required'    => false,
                'person'      => $options['person'],
                'constraints' => [
                    new Assert\Valid(),
                ],
            ])
        ;

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseMassActionParamsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'actions'         => ['mark_as_spam', 'delete'],
            'data_class'      => Ticket::class,
            'agent_interface' => true,
            'constraints'     => [
                new AppAssert\Ticket\TicketLayout([
                    'context' => 'agent',
                ]),
            ],
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        if (!$event->getData() instanceof Ticket) {
            $event->setData(new Ticket());
        }

        $form = $event->getForm();
        $form->add('set_followers', TicketParticipantsType::class, [
            'owner'           => $event->getData(),
            'agent_interface' => $form->getConfig()->getOption('agent_interface'),
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!$data instanceof Ticket) {
            return;
        }

        $message = $form->get('reply')->getData();
        if ($message instanceof TicketMessage) {
            $data->addMessage($message);
        }

        if (BaseMassActionParamsType::hasAction($event, 'mark_as_spam')) {
            $data->setStatus('hidden.spam');
        }
        if (BaseMassActionParamsType::hasAction($event, 'delete')) {
            $data->setStatus('hidden.deleted');
        }
    }
}
