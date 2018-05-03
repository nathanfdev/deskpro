<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse;

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitLockoutGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RateLimitLockoutGroupType.
 */
class RateLimitLockoutGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('lockout_time', NumberType::class, [
            'property_path' => 'lockoutTime',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RateLimitLockoutGroup::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseRateLimitGroupType::class;
    }
}
