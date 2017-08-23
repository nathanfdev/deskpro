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

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonEmail\PersonEmailType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BasePersonType.
 */
class BasePersonType extends AbstractType
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
            ->add('name', TextType::class)
            ->add('primary_email', new PersonEmailType($builder->getData(), $this->em))
            ->add('emails', CollectionType::class, [
                'type'           => new PersonEmailType($builder->getData(), $this->em),
                'allow_add'      => true,
                'allow_delete'   => true,
                'delete_empty'   => true,
                'by_reference'   => false,
                'error_bubbling' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSyncEmails']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
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

        if (!empty($data['primary_email'])) {
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
}
