<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class RateLimitGroupType.
 */
class BaseRateLimitGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', ApiBooleanType::class)
            ->add('limit', NumberType::class)
            ->add('time', NumberType::class)
        ;
    }
}
