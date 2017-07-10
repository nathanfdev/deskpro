<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
