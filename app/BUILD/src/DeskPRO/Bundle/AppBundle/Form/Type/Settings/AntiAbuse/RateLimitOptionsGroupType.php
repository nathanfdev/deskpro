<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse;

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RateLimitOptionsGroupType.
 */
class RateLimitOptionsGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('response', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    RateLimitOptionsGroup::RESPONSE_CAPTCHA,
                    RateLimitOptionsGroup::RESPONSE_LOCKOUT,
                ],
            ])
            ->add('lockout_time', NumberType::class, [
                'property_path' => 'lockoutTime',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RateLimitOptionsGroup::class,
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
