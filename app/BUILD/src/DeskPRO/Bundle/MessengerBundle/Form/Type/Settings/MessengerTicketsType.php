<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerTickets;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MessengerTicketsType.
 */
class MessengerTicketsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', ApiBooleanType::class)

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => MessengerTickets::class,
            ])
        ;
    }
}
