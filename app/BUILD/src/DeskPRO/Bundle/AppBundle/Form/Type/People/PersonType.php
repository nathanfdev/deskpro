<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\ContactData\ContactDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\UsergroupsType;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppConstraints;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class PersonType.
 */
class PersonType extends AbstractType
{
    /**
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * PersonType constructor.
     *
     * @param CustomFieldManager $fieldManager
     * @param TokenStorage       $tokenStorage
     */
    public function __construct(CustomFieldManager $fieldManager, TokenStorage $tokenStorage)
    {
        $this->fieldManager = $fieldManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title_prefix', TextType::class, [
                'required' => false,
            ])
            ->add('first_name', TextType::class, [
                'required' => false,
            ])
            ->add('last_name', TextType::class, [
                'required' => false,
            ])
            ->add('override_display_name', TextType::class, [
                'required' => false,
            ])
            ->add('summary', TextType::class, [
                'required' => false,
            ])
            ->add('timezone', TextType::class, [
                'required' => false,
            ])
            ->add('organization', EntityType::class, [
                'class'    => Organization::class,
                'required' => false,
            ])
            ->add('organization_position', TextType::class, [
                'required' => false,
            ])
            ->add('organization_manager', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('language', EntityType::class, [
                'class'    => Language::class,
                'required' => false,
            ])
            ->add('labels', LabelsCollectionType::class, [
                'labels_class'   => LabelPerson::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'person',
                'required'       => false,
            ])
            ->add('is_agent', ApiBooleanType::class, [
                'required'     => false,
                'by_reference' => false,
            ])
            ->add('user_groups', UsergroupsType::class, [
                'is_agent_group' => false,
                'owner'          => $builder->getData(),
                'required'       => false,
            ])
            ->add('agent_groups', UsergroupsType::class, [
                'is_agent_group' => true,
                'owner'          => $builder->getData(),
                'required'       => false,
            ])
            ->add('fields', CombinedType::class, [
                'forms'          => $this->getCustomDataFields($options),
                'error_bubbling' => false,
                'required'       => false,
            ])
            ->add('contact_data', ContactDataType::class, [
                'owner'          => $builder->getData(),
                'parent_builder' => $builder,
                'required'       => false,
            ])
            ->add('teams', EntityType::class, [
                'class'        => AgentTeam::class,
                'multiple'     => true,
                'by_reference' => false,
                'required'     => false,
            ])
            ->add('primary_team', EntityType::class, [
                'class'    => AgentTeam::class,
                'required' => false,
            ])
            ->add('agent_data', PersonAgentDataType::class, [
                'property_path' => 'agentData',
                'required'      => false,
                'person'        => $builder->getData(),
            ])
            ->add('phone_numbers', CollectionType::class, [
                'required'      => false,
                'allow_add'     => true,
                'allow_delete'  => true,
                'entry_type'    => PersonPhoneNumberType::class,
                'entry_options' => [
                    'error_bubbling' => false,
                    'person'         => $builder->getData(),
                    'constraints'    => [
                        new AppConstraints\PhoneNumber(),
                    ],
                ],
            ])
            ->add('brands', EntityType::class, [
                'class'        => Brand::class,
                'multiple'     => true,
                'by_reference' => false,
                'required'     => false,
            ])
            ->add('is_disabled', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('preferences', PersonPreferencesType::class, [
                'required' => false,
                'person'   => $builder->getData(),
            ])
        ;

        // resolve field name aliases
        $fieldNameResolver = $this->fieldManager->getFieldNameResolver(CustomDefPerson::class);
        $builder->addEventSubscriber($fieldNameResolver);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'agent_interface' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BasePersonType::class;
    }

    /**
     * Sync `name`, `first_name` and `last_name` props.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Person $person */
        $person = $form->getData();
        $data   = $event->getData();

        if (!empty($data['name'])) {
            $person->setName($data['name']);
            $event->setData(array_merge($data, [
                'first_name' => $person->getFirstName(),
                'last_name'  => $person->getLastName(),
            ]));
        }

        /** @var \Application\DeskPRO\Entity\Person $user */
        $token = $this->tokenStorage->getToken();
        $sessionPerson  = $token ? $token->getUser() : null;
        $formPerson = $form->getData();

        if ($sessionPerson instanceof Person
            && $formPerson instanceof Person
            && (!$formPerson->isAgent() || $sessionPerson->isAdmin() || $sessionPerson === $formPerson)
        ) {
            $form->add('password', TextType::class, [
                'required' => false,
            ]);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof Person) {
            return;
        }

        if (!$data->isAgent()) {
            $data->setAgentData(null);
        }
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function getCustomDataFields(array $options)
    {
        $defs   = $this->fieldManager->getAvailablePersonDefs();
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
                ],
            ];
        }

        return $fields;
    }
}
