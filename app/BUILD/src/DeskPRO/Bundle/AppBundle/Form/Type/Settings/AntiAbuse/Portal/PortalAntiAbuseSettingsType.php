<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\Portal;

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalAntiAbuseSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PortalAntiAbuseSettingsType.
 */
class PortalAntiAbuseSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account_rate_limit', PortalAccountRateLimitType::class, [
                'property_path' => 'accountRateLimit',
                'required'      => false,
            ])
            ->add('agent_rate_limit', PortalAgentRateLimitType::class, [
                'property_path' => 'agentRateLimit',
                'required'      => false,
            ])
            ->add('user_rate_limit', PortalUserRateLimitType::class, [
                'property_path' => 'userRateLimit',
                'required'      => false,
            ])
            ->add('guest_rate_limit', PortalUserRateLimitType::class, [
                'property_path' => 'guestRateLimit',
                'required'      => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PortalAntiAbuseSettings::class,
        ]);
    }
}
