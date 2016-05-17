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

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\ContactData\ContactDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonEmail\PersonEmailType;
use DeskPRO\Bundle\AppBundle\Form\Type\UsergroupsType;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PersonType.
 */
class PersonType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var CustomFieldManager
     */
    private $field_manager;

    /**
     * PersonType constructor.
     *
     * @param EntityManager      $em
     * @param CustomFieldManager $field_manager
     */
    public function __construct(EntityManager $em, CustomFieldManager $field_manager)
    {
        $this->em            = $em;
        $this->field_manager = $field_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('password', TextType::class)
            ->add('title_prefix', TextType::class)
            ->add('first_name', TextType::class)
            ->add('last_name', TextType::class)
            ->add('override_display_name', TextType::class)
            ->add('summary', TextType::class)
            ->add('timezone', TextType::class)
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
            ])
            ->add('organization_position', TextType::class)
            ->add('language', EntityType::class, [
                'class' => Language::class,
            ])
            ->add('labels', LabelsCollectionType::class, [
                'labels_class'   => LabelPerson::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'person',
            ])
            ->add('primary_email', new PersonEmailType($builder->getData(), $this->em))
            ->add('emails', CollectionType::class, [
                'type'           => new PersonEmailType($builder->getData(), $this->em),
                'allow_add'      => true,
                'allow_delete'   => true,
                'delete_empty'   => true,
                'by_reference'   => false,
                'error_bubbling' => false,
            ])
            ->add('user_groups', UsergroupsType::class, [
                'is_agent_group' => false,
                'owner'          => $builder->getData(),
            ])
            ->add('agent_groups', UsergroupsType::class, [
                'is_agent_group' => true,
                'owner'          => $builder->getData(),
            ])
            ->add('fields', CombinedType::class, [
                'forms'          => $this->getCustomDataFields($options),
                'error_bubbling' => false,
            ])
            ->add('contact_data', ContactDataType::class, [
                'owner'          => $builder->getData(),
                'parent_builder' => $builder,
            ])
            ->add('teams', EntityType::class, [
                'class'        => AgentTeam::class,
                'multiple'     => true,
                'by_reference' => false,
            ])
            ->add('primary_team', EntityType::class, [
                'class' => AgentTeam::class,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSyncEmails']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSyncName']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'      => Person::class,
                'agent_interface' => false,
            ])
        ;
    }

    /**
     * Sync `emails` and `primary email` props.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSyncEmails(FormEvent $event)
    {
        /** @var Person $person */
        $person = $event->getForm()->getData();
        $data   = $event->getData();

        if (isset($data['primary_email'])) {
            if (!isset($data['emails'])) {
                $data['emails'] = $person->getEmailAddresses();
            }
            if (!in_array($data['primary_email'], $data['emails'])) {
                $data['emails'][] = $data['primary_email'];
            }
        } else {
            if (!empty($data['emails'])) {
                $data['primary_email'] = $data['emails'][0];
            } elseif (isset($data['emails'])) {
                $data['primary_email'] = '';
            }
        }

        $event->setData($data);
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
                'first_name' => $person->first_name,
                'last_name'  => $person->last_name,
            ]));
        }
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function getCustomDataFields(array $options)
    {
        $field_defs  = $this->field_manager->getAvailablePersonDefs();
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
                ],
            ];
        }

        return $form_fields;
    }
}
