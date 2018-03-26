<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Helper\WidgetJwtDecoder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChatCreateType.
 */
class ChatCreateType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SetPersonListener
     */
    private $personListener;

    /**
     * @var WidgetSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var PermissionsManager
     */
    private $permissionsManager;

    /**
     * @var WidgetJwtDecoder
     */
    private $jwtDecoder;

    /**
     * Constructor.
     *
     * @param EntityManager          $em
     * @param SetPersonListener      $personListener
     * @param WidgetSettingsResolver $settingsResolver
     * @param CustomFieldManager     $fieldManager
     * @param BrandStack             $brandStack
     * @param PermissionsManager     $permissionsManager
     * @param WidgetJwtDecoder       $jwtDecoder
     */
    public function __construct(
        EntityManager          $em,
        SetPersonListener      $personListener,
        WidgetSettingsResolver $settingsResolver,
        CustomFieldManager     $fieldManager,
        BrandStack             $brandStack,
        PermissionsManager     $permissionsManager,
        WidgetJwtDecoder       $jwtDecoder
    ) {
        $this->em                 = $em;
        $this->personListener     = $personListener;
        $this->settingsResolver   = $settingsResolver;
        $this->fieldManager       = $fieldManager;
        $this->brandStack         = $brandStack;
        $this->permissionsManager = $permissionsManager;
        $this->jwtDecoder         = $jwtDecoder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $brand        = $this->brandStack->getActive()->getBrand();
        $brandOptions = $this->settingsResolver->getWidgetBrandOptions($brand);

        $nameConstraints = [];
        if ($brandOptions->getChat()->isRequiredName()) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onForceName'], 100);
            $nameConstraints[] = new Assert\NotBlank();
        }

        $emailConstraints = [new Assert\Email(['strict' => true])];
        if ($brandOptions->getChat()->isRequiredEmail()) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onForceEmail'], 100);
            $emailConstraints[] = new Assert\NotBlank();
        }

        $builder
            ->add('name', TextType::class, [
                'property_path' => 'person_name',
                'required'      => false,
                'constraints'   => $nameConstraints,
            ])
            ->add('email', EmailType::class, [
                'property_path' => 'person_email',
                'required'      => false,
                'constraints'   => $emailConstraints,
            ])
            ->add('fields', CombinedType::class, [
                'forms'          => $this->getCustomDataFields(),
                'error_bubbling' => false,
            ])
            ->add('jwt', HiddenType::class, [
                'mapped'         => false,
                'error_bubbling' => true,
                'constraints'    => [
                    new AppAssert\JwtToken([
                        'required' => $this->settingsResolver->isJwtRequired($brand),
                        'secret'   => $this->settingsResolver->getJwtSecret($brand),
                    ]),
                ],
            ])
        ;

        $permissionsBag       = $this->permissionsManager->getPortalPermissionsBag($options['person']);
        $allowedDepartmentIds = $permissionsBag->getAllowedChatDepartmentIds();

        $builder->add('chat_department', EntityType::class, [
            'class'         => Department::class,
            'property_path' => 'department',
            'query_builder' => function (EntityRepository $er) use ($allowedDepartmentIds, $brand) {
                $qb = $er
                    ->createQueryBuilder('d')
                    ->join('d.brands', 'b')
                    ->where(
                        'd.is_chat_enabled = true',
                        'd.id IN (:allowed_department_ids)',
                        'b.id IN(:brand)'
                    )
                    ->setParameter('allowed_department_ids', $allowedDepartmentIds)
                    ->setParameter('brand', $brand)
                ;

                return $qb;
            },
            'constraints' => [
                new Assert\NotNull(),
                new AppAssert\LeafDepartment(),
            ],
        ]);

        $builder->addEventSubscriber($this->personListener);
        $builder->addEventSubscriber(new AutoSetShouldSentTranscriptListener());

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'                    => ChatConversation::class,
                'csrf_protection'               => false,
                'csrf_double_submit_protection' => false,
            ])
            ->setRequired(['person', 'visitor_id'])
            ->setAllowedTypes('person', ['null', Person::class])
            ->setAllowedTypes('visitor_id', ['null', 'string'])
        ;
    }

    /**
     * Force name field if it's required.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onForceName(FormEvent $event)
    {
        $data = $event->getData();
        if (!isset($data['name'])) {
            $data['name'] = '';
        }

        $event->setData($data);
    }

    /**
     * Form fields are optional but we need to handle email field anyway if chat email validation is enabled.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onForceEmail(FormEvent $event)
    {
        $data = $event->getData();
        if (!isset($data['email'])) {
            $data['email'] = '';
        }

        $event->setData($data);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data   = $event->getData();
        $form   = $event->getForm();
        $person = $form->getConfig()->getOption('person');

        // try jwt token
        if (!isset($data['jwt']) || !is_string($data['jwt'])) {
            $data['jwt'] = '';
        }
        if ($data['jwt']) {
            $decodedJwt = $this->jwtDecoder->decodeJwtPayload($data['jwt']);

            // set person email from the jwt token
            foreach (['email', 'user_email'] as $option) {
                if (isset($decodedJwt[$option])) {
                    $data['email'] = $decodedJwt[$option];
                }
            }

            // set person name from the jwt token
            foreach (['name', 'user_name'] as $option) {
                if (isset($decodedJwt[$option])) {
                    $data['name'] = $decodedJwt[$option];
                }
            }

            // set person info from 'person_id' option of the jwt token
            if (isset($decodedJwt['person_id'])) {
                $person = $this->em->find(Person::class, $decodedJwt['person_id']);
                if ($person) {
                    $data['email'] = $person->getPrimaryEmailAddress();
                    $data['name']  = $person->getDisplayName();
                }
            }
        }

        // make sure that department field was submitted
        if (!isset($data['chat_department'])) {
            $data['chat_department'] = null;
        }

        // if session has person entity we can assign it to the chat
        // uses if chat settings require user to be logged in
        if ($person instanceof Person) {
            $data['email'] = $person->getPrimaryEmailAddress();
            $data['name']  = $person->getDisplayName();
        }

        $event->setData($data);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var ChatConversation $conversation */
        $conversation = $event->getData();
        $conversation->setVisitorId($form->getConfig()->getOption('visitor_id'));

        // if a jwt token was provided, then it means the person is already validated
        // set session person as well
        $jwtPayload = $form->get('jwt')->getData();
        $person     = $conversation->getPerson();
        $session    = $conversation->getSession();

        if ($person && $session && $decodedJwt = $this->jwtDecoder->decodeJwtPayload($jwtPayload)) {
            $matched = false;

            // check payload by person id
            if (isset($decodedJwt['person_id']) && (int) $decodedJwt['person_id'] === $person->getId()) {
                $matched = true;
            }

            // check payload by person email
            foreach (['email', 'user_email'] as $option) {
                if (isset($decodedJwt[$option]) && $person->hasEmailAddress($decodedJwt[$option])) {
                    $matched = true;
                }
            }

            if ($matched) {
                $session->setPerson($person);
            }
        }
    }

    /**
     * @return array
     */
    private function getCustomDataFields()
    {
        $defs   = $this->fieldManager->getAvailableChatDefs();
        $fields = [];

        foreach ($defs as $def) {
            $fields[] = [
                'name'    => $def->getId(),
                'type'    => CustomDataType::class,
                'options' => [
                    'custom_def'      => $def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => false,
                    'inline'          => true,
                ],
            ];
        }

        return $fields;
    }
}
