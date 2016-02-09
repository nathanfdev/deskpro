<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form can accept person "id", "email address" or {"email": "xxx", "name": "xxx"}.
 */
class PersonIdentityType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'text', [
                'required' => false,
            ])
            ->add('name', 'text', [
                'label'    => $options['label_name'],
                'required' => false,
            ])
            ->add('email', 'text', [
                'label'    => $options['label_email'],
                'required' => false,
            ])
        ;

        foreach (['id', 'name', 'email'] as $available_field) {
            if (!in_array($available_field, $options['available_fields'])) {
                $builder->remove($available_field);
            }
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPrepare']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetData']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPrepare(FormEvent $event)
    {
        $form   = $event->getForm();
        $person = $form->getConfig()->getOption('person');

        if ($person) {
            $event->setData($person);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSetData(FormEvent $event)
    {
        $data = $event->getData();

        /** @var \Application\DeskPRO\EntityRepository\Person $person_repository */
        $person_repository = $this->em->getRepository('DeskPRO:Person');

        if (is_array($data)) {
            $event->setData($person_repository->findOneBy(['email' => $data['email']]));
        } elseif (is_numeric($data)) {
            $event->setData($person_repository->find((int) $data));
        } else {
            $event->setData($person_repository->findOneBy(['email' => $data]));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'person'           => null,
                'label_name'       => '',
                'label_email'      => '',
                'data_class'       => 'Application\\DeskPRO\\Entity\\Person',
                'available_fields' => ['id', 'name', 'email'],
            ])
            ->setAllowedTypes([
                'person' => 'Application\\DeskPRO\\Entity\\Person',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deskpro_person_identity';
    }
}
