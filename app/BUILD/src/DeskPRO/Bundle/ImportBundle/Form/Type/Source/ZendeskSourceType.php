<?php

namespace DeskPRO\Bundle\ImportBundle\Form\Type\Source;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ZendeskSourceType.
 */
class ZendeskSourceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account', ZendeskAccountType::class)
            ->add('start_time', TextType::class)
            ->add('ticket_brand_field', TextType::class)
        ;
    }
}
