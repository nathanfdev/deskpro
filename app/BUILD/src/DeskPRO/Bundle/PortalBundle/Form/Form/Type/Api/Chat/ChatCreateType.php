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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\LeafDepartment;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
     * Constructor.
     *
     * @param SetPersonListener      $personListener
     * @param WidgetSettingsResolver $settingsResolver
     * @param CustomFieldManager     $fieldManager
     * @param BrandStack             $brandStack
     * @param PermissionsManager     $permissionsManager
     */
    public function __construct(
        SetPersonListener      $personListener,
        WidgetSettingsResolver $settingsResolver,
        CustomFieldManager     $fieldManager,
        BrandStack             $brandStack,
        PermissionsManager     $permissionsManager
    ) {
        $this->personListener     = $personListener;
        $this->settingsResolver   = $settingsResolver;
        $this->fieldManager       = $fieldManager;
        $this->brandStack         = $brandStack;
        $this->permissionsManager = $permissionsManager;
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
                new LeafDepartment(),
            ],
        ]);

        $builder->addEventSubscriber($this->personListener);
        $builder->addEventSubscriber(new AutoSetShouldSentTranscriptListener());

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetPersonDataFromSession']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onForceDepartment']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetVisitorId']);
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
     * Ensure that department field was submitted.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onForceDepartment(FormEvent $event)
    {
        $data = $event->getData();
        if (!isset($data['chat_department'])) {
            $data['chat_department'] = null;
        }

        $event->setData($data);
    }

    /**
     * If session has person entity we can assign it to the chat.
     * Uses if chat settings require user to be logged in.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetPersonDataFromSession(FormEvent $event)
    {
        $form   = $event->getForm();
        $person = $form->getConfig()->getOption('person');

        if ($person instanceof Person) {
            $event->setData(array_merge($event->getData(), [
                'email' => $person->getPrimaryEmailAddress(),
                'name'  => $person->getDisplayName(),
            ]));
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetVisitorId(FormEvent $event)
    {
        /** @var ChatConversation $conversation */
        $conversation = $event->getData();
        $conversation->setVisitorId($event->getForm()->getConfig()->getOption('visitor_id'));
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
