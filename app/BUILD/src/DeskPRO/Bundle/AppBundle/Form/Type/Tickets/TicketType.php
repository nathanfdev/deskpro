<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Brand;
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
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketStatusDataService;
use DeskPRO\Bundle\AppBundle\Form\BrandFormHelper;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants\TicketParticipantsType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as CoreDateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
     * @var EntityManager
     */
    private $em;

    /**
     * @var TicketStatusDataService
     */
    private $ticketStatuses;

    /**
     * Constructor.
     *
     * @param CustomFieldManager      $fieldManager
     * @param BrandFormHelper         $helper
     * @param EntityManager           $em
     * @param TicketStatusDataService $ticketStatuses
     */
    public function __construct(
        CustomFieldManager $fieldManager,
        BrandFormHelper $helper,
        EntityManager $em,
        TicketStatusDataService $ticketStatuses)
    {
        $this->ticketStatuses = $ticketStatuses;
        $this->fieldManager   = $fieldManager;
        $this->helper         = $helper;
        $this->em             = $em;
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
                'choices'           => $this->getAllTicketStatusCodes(),
                'mapped'            => false,
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
            ->add('date_created', CoreDateTimeType::class, [
                'widget'   => 'single_text',
                'required' => false,
            ])
        ;

        if ($builder->getOption('admin_api_key_request')) {
            $builder
                ->add('date_feedback_rating', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_resolved', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_archived', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_first_agent_assign', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_first_agent_reply', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_last_agent_reply', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_last_user_reply', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_agent_waiting', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_user_waiting', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_status', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_on_hold', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('date_locked', CoreDateTimeType::class, [
                    'widget'   => 'single_text',
                    'required' => false,
                ])
                ->add('total_user_waiting', IntegerType::class, [
                    'required'      => false,
                    'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_HALF_UP,
                ])
                ->add('total_to_first_reply', IntegerType::class, [
                    'required'      => false,
                    'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_HALF_UP,
                ])
            ;
        }

        $brands = $this->em->getRepository(Brand::class)->findAll();
        if (count($brands) > 1) {
            $builder->add('brand', EntityType::class, [
                'class'    => Brand::class,
                'required' => false,
            ]);
        }

        // resolve field name aliases
        $fieldNameResolver = $this->fieldManager->getFieldNameResolver(CustomDefTicket::class);
        $builder->addEventSubscriber($fieldNameResolver);

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefaults([
                'data_class'            => Ticket::class,
                'agent_interface'       => false,
                'admin_api_key_request' => false,
            ])
            ->setAllowedTypes('admin_api_key_request', 'bool')
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
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        /** @var Ticket $ticket */
        $ticket = $form->getData();

        // add messages
        if (isset($data['message'])) {
            $message = $ticket->getMessages()->first();
            if (!$message) {
                $message = new TicketMessage();
                $ticket->addMessage($message);
            }

            $form->add('message', TicketMessageType::class, [
                'mapped'                => false,
                'ticket'                => $form->getData(),
                'person'                => $form->getConfig()->getOption('person'),
                'ticket_message'        => $message,
                'admin_api_key_request' => $form->getConfig()->getOption('admin_api_key_request'),
            ]);
        }

        if (isset($data['messages'])) {
            $form->add('messages', CollectionType::class, [
                'mapped'        => false,
                'allow_add'     => true,
                'entry_type'    => TicketMessageType::class,
                'entry_options' => [
                    'ticket'                => $form->getData(),
                    'person'                => $form->getConfig()->getOption('person'),
                    'admin_api_key_request' => $form->getConfig()->getOption('admin_api_key_request'),
                ],
            ]);
        }

        // update department field if a brand was submitted
        // should be able to get just related departments
        if (isset($data['brand'])) {
            $form->remove('department');
            $form->add('department', EntityType::class, [
                'class'         => Department::class,
                'query_builder' => function (EntityRepository $er) use ($data) {
                    return $er
                        ->createQueryBuilder('d')
                        ->join('d.brands', 'b')
                        ->where('b.id = :brand')
                        ->setParameter('brand', $data['brand'])
                    ;
                },
            ]);
        }

        $adminFields = [
            'date_feedback_rating',
            'date_resolved',
            'date_archived',
            'date_first_agent_assign',
            'date_first_agent_reply',
            'date_last_agent_reply',
            'date_last_user_reply',
            'date_agent_waiting',
            'date_user_waiting',
            'date_status',
            'date_on_hold',
            'date_locked',
            'total_user_waiting',
            'total_to_first_reply',
        ];

        foreach ($adminFields as $adminField) {
            if (!isset($data[$adminField]) && $form->has($adminField)) {
                $form->remove($adminField);
            }
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var Ticket $ticket */
        $ticket = $event->getData();

        $status = $form->get('status')->getData();
        if ($status) {
            $ticket->setTicketStatus($this->ticketStatuses->findStatus($status));
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

    /**
     * @return array
     */
    private function getAllTicketStatusCodes()
    {
        return array_map(function ($item) {
            return $item['value'];
        }, $this->ticketStatuses->getFormOptions());
    }
}
