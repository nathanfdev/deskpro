<?php

namespace DeskPRO\Bundle\ImportBundle\Form\Type\Source;

use DeskPRO\Bundle\AppBundle\Form\DataTransformer\TextDateTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;

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
            ->add('start_time', DateType::class, [
                'required'        => false,
                'widget'          => 'single_text',
                'format'          => 'yyyy-MM-dd',
            ])
            ->add('ticket_brand_field', TextType::class)
        ;

        $builder->get('start_time')->addModelTransformer(new ReversedTransformer(new TextDateTransformer()));
    }
}
