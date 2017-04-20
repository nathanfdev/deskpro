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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\GeneralSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GeneralSettingsType.
 */
class GeneralSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('site_name', TextType::class)
            ->add('brand_name', TextType::class)
            ->add('site_url', UrlType::class)
            ->add('deskpro_name', TextType::class)
            ->add('deskpro_url', UrlType::class)
            ->add('apps_feedback', ApiBooleanType::class)
            ->add('apps_kb', ApiBooleanType::class)
            ->add('apps_news', ApiBooleanType::class)
            ->add('apps_downloads', ApiBooleanType::class)
            ->add('apps_guides', ApiBooleanType::class)
            ->add('iface_portal', ApiBooleanType::class)
            ->add('iface_widget', ApiBooleanType::class)
            ->add('show_ratings', ApiBooleanType::class)
            ->add('show_ratings_min_votes', IntegerType::class)
            ->add('publish_comments', ApiBooleanType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GeneralSettings::class,
        ]);
    }
}
