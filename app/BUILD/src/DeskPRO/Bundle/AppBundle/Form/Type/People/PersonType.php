<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\ContactData\ContactDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\PhoneNumberType;
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
     * PersonType constructor.
     *
     * @param CustomFieldManager $fieldManager
     */
    public function __construct(CustomFieldManager $fieldManager)
    {
        $this->fieldManager = $fieldManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', TextType::class, [
                'required' => false,
            ])
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
                'entry_type'    => PhoneNumberType::class,
                'entry_options' => [
                    'error_bubbling' => false,
                    'person'         => $builder->getData(),
                    'constraints'    => [
                        new AppConstraints\PhoneNumber(),
                    ],
                ],
            ])
        ;

        // resolve field name aliases
        $fieldNameResolver = $this->fieldManager->getFieldNameResolver(CustomDefPerson::class);
        $builder->addEventSubscriber($fieldNameResolver);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSyncName']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onUnsetAgentData'], 100);
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
    public function onSyncName(FormEvent $event)
    {
        /** @var Person $person */
        $person = $event->getForm()->getData();
        $data   = $event->getData();

        if (!empty($data['name'])) {
            $person->setName($data['name']);
            $event->setData(array_merge($data, [
                'first_name' => $person->getFirstName(),
                'last_name'  => $person->getLastName(),
            ]));
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onUnsetAgentData(FormEvent $event)
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
