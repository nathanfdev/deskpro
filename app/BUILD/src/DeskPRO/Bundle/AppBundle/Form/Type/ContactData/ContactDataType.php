<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContactDataType.
 */
class ContactDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(ContactDataAbstract::TYPE_PHONE, ContactDataCollectionType::class, [
                'entry_type' => PhoneType::class,
                'owner'      => $options['owner'],
                'required'   => false,
            ])
            ->add(ContactDataAbstract::TYPE_WEBSITE, ContactDataCollectionType::class, [
                'entry_type' => WebsiteType::class,
                'owner'      => $options['owner'],
                'required'   => false,
            ])
            ->add(ContactDataAbstract::TYPE_INSTANT_MESSAGE, ContactDataCollectionType::class, [
                'entry_type' => InstantMessageType::class,
                'owner'      => $options['owner'],
                'required'   => false,
            ])
            ->add(ContactDataAbstract::TYPE_TWITTER, ContactDataCollectionType::class, [
                'entry_type' => TwitterType::class,
                'owner'      => $options['owner'],
                'required'   => false,
            ])
            ->add(ContactDataAbstract::TYPE_LINKED_IN, ContactDataCollectionType::class, [
                'entry_type' => LinkedInType::class,
                'owner'      => $options['owner'],
                'required'   => false,
            ])
            ->add(ContactDataAbstract::TYPE_FACEBOOK, ContactDataCollectionType::class, [
                'entry_type' => FacebookType::class,
                'owner'      => $options['owner'],
                'required'   => false,
            ])
            ->add(ContactDataAbstract::TYPE_ADDRESS, ContactDataCollectionType::class, [
                'entry_type' => AddressType::class,
                'owner'      => $options['owner'],
                'required'   => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onSetData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onMergeData']);

        /** @var FormBuilderInterface $parent_builder */
        $parent_builder = $options['parent_builder'];
        $parent_builder->addEventListener(FormEvents::POST_SUBMIT, new ContactDataViolationMapper($builder->getName()), -1);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'error_bubbling' => false,
                'mapped'         => false,
            ])
            ->setRequired(['owner', 'parent_builder'])
            ->setAllowedTypes('owner', [Person::class, Organization::class])
            ->setAllowedTypes('parent_builder', FormBuilderInterface::class)
        ;
    }

    /**
     * Slice contact data collection by contact type groups.
     *
     * @param FormEvent $event
     */
    public function onSetData(FormEvent $event)
    {
        $form  = $event->getForm();
        $owner = $form->getConfig()->getOption('owner');

        /** @var ContactDataAbstract[] $data */
        $data = $owner->getContactData();
        if (!$data) {
            $data = new ArrayCollection();
        }

        $grouped_data = [];
        $data_groups  = [];

        foreach ($data as $item) {
            $data_groups[$item->getContactType()][] = $item;
        }

        foreach ($form->all() as $form_group) {
            $entry_type = $form_group->getConfig()->getOption('entry_type');
            if (!$entry_type) {
                continue;
            }

            $contact_type = $entry_type::getContactType();

            $data_group = isset($data_groups[$contact_type]) ? $data_groups[$contact_type] : [];
            $data_group = new ArrayCollection($data_group);

            $grouped_data[$form_group->getName()] = $data_group;
        }

        $event->setData($grouped_data);
    }

    /**
     * Merge groups to a single collection.
     *
     * @param FormEvent $event
     */
    public function onMergeData(FormEvent $event)
    {
        $form  = $event->getForm();
        $owner = $form->getConfig()->getOption('owner');

        /** @var ArrayCollection $data */
        $data = $owner->getContactData();

        foreach ($form->all() as $form_group) {
            $entry_type = $form_group->getConfig()->getOption('entry_type');
            if (!$entry_type) {
                continue;
            }

            /** @var ArrayCollection $group_data */
            $group_data   = $form_group->getData();
            $contact_type = $entry_type::getContactType();

            /** @var ContactDataAbstract $data_item */
            foreach ($group_data as $data_item) {
                if (!$data->contains($data_item)) {
                    $data->add($data_item);
                }
            }

            foreach ($data as $data_item) {
                if ($data_item->getContactType() === $contact_type && !$group_data->contains($data_item)) {
                    $data->removeElement($data_item);
                }
            }
        }
    }
}
