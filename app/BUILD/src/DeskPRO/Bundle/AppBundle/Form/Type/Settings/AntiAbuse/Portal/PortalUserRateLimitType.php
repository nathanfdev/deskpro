<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\Portal;

use DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\RateLimitOptionsGroupType;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\Portal\PortalUserRateLimit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PortalUserRateLimitType.
 */
class PortalUserRateLimitType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login_settings', RateLimitOptionsGroupType::class, [
                'property_path' => 'loginSettings',
            ])
            ->add('submit_ticket', RateLimitOptionsGroupType::class, [
                'property_path' => 'submitTicket',
            ])
            ->add('submit_feedback', RateLimitOptionsGroupType::class, [
                'property_path' => 'submitFeedback',
            ])
            ->add('submit_comment', RateLimitOptionsGroupType::class, [
                'property_path' => 'submitComment',
            ])
            ->add('upload_attachment', RateLimitOptionsGroupType::class, [
                'property_path' => 'uploadAttachment',
            ])
            ->add('share_content', RateLimitOptionsGroupType::class, [
                'property_path' => 'shareContent',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PortalUserRateLimit::class,
        ]);
    }
}
