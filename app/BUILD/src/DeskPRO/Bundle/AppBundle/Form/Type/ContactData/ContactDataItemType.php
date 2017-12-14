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

namespace DeskPRO\Bundle\AppBundle\Form\Type\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationContactData;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonContactData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContactDataItemType.
 */
class ContactDataItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('comment', TextareaType::class, [
            'required' => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onCreateDataClass']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSetContactType']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['owner', 'contact_type'])
            ->setDefaults([
                'data_class'     => ContactDataAbstract::class,
                'error_bubbling' => false,
            ])
            ->setAllowedTypes('contact_type', 'string')
            ->setAllowedTypes('owner', [Person::class, Organization::class])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onCreateDataClass(FormEvent $event)
    {
        $form  = $event->getForm();
        $owner = $form->getConfig()->getOption('owner');

        if (!$event->getData()) {
            if ($owner instanceof Person) {
                $data = new PersonContactData();
                $data->setPerson($owner);
            } elseif ($owner instanceof Organization) {
                $data = new OrganizationContactData();
                $data->setOrganization($owner);
            } else {
                $data = null;
            }

            $event->setData($data);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSetContactType(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var ContactDataAbstract $data */
        $data = $event->getData();
        $data->setContactType($config->getOption('contact_type'));
    }
}
