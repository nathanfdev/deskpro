<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\Portal;

use DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\RateLimitOptionsGroupType;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalAgentRateLimit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PortalUserRateLimitType.
 */
class PortalAgentRateLimitType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('login_settings', RateLimitOptionsGroupType::class, [
            'property_path' => 'loginSettings',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PortalAgentRateLimit::class,
        ]);
    }
}
