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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('phone', ContactDataCollectionType::class, [
                'entry_type' => PhoneType::class,
                'owner'      => $options['owner'],
            ])
            ->add('website', ContactDataCollectionType::class, [
                'entry_type' => WebsiteType::class,
                'owner'      => $options['owner'],
            ])
            ->add('im', ContactDataCollectionType::class, [
                'entry_type' => InstantMessageType::class,
                'owner'      => $options['owner'],
            ])
            ->add('twitter', ContactDataCollectionType::class, [
                'entry_type' => TwitterType::class,
                'owner'      => $options['owner'],
            ])
            ->add('linked_in', ContactDataCollectionType::class, [
                'entry_type' => LinkedInType::class,
                'owner'      => $options['owner'],
            ])
            ->add('facebook', ContactDataCollectionType::class, [
                'entry_type' => FacebookType::class,
                'owner'      => $options['owner'],
            ])
            ->add('address', ContactDataCollectionType::class, [
                'entry_type' => AddressType::class,
                'owner'      => $options['owner'],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onSetData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onMergeData']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(['owner'])
            ->setAllowedTypes([
                'owner' => [Person::class, Organization::class],
            ])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetData(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var ContactDataAbstract[] $data */
        $data = $event->getData();
        if (!$data) {
            $data = new ArrayCollection();
        }

        $data_groups = [];
        foreach ($data as $data_item) {
            $data_groups[$data_item->getContactType()][] = $data_item;
        }

        foreach ($form->all() as $form_group) {
            $entry_type = $form_group->getConfig()->getOption('entry_type');
            if ($entry_type instanceof ContactDataTypeGroupInterface) {
                $contact_type = $entry_type::getContactType();

                $data_group = isset($data_groups[$contact_type]) ? $data_groups[$contact_type] : [];
                $data_group = new ArrayCollection($data_group);

                $form_group->setData($data_group);
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onMergeData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = [];

        foreach ($form->all() as $form_group) {
            foreach ($form_group->getData() as $data_item) {
                $data[] = $data_item;
            }
        }

        $event->setData($data);
    }
}
