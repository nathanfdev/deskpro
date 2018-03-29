<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\Portal;

use DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\RateLimitLockoutGroupType;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalAccountRateLimit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PortalAccountRateLimitType.
 */
class PortalAccountRateLimitType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('registration_settings', RateLimitLockoutGroupType::class, [
                'property_path' => 'registrationSettings',
            ])
            ->add('reset_password_settings', RateLimitLockoutGroupType::class, [
                'property_path' => 'resetPasswordSettings',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PortalAccountRateLimit::class,
        ]);
    }
}
