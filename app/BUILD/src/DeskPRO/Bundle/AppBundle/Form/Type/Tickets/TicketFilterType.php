<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketFilterType.
 */
class TicketFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('display_order', IntegerType::class, [
                'required' => false,
            ])
            ->add('ticket_filter_set', EntityType::class, [
                'property_path' => 'filter_set',
                'required'      => false,
                'class'         => TicketFilterSet::class,
            ])
            ->add('term', 'term_engine_term', [
                'required' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TicketFilter::class,
        ]);
    }
}
