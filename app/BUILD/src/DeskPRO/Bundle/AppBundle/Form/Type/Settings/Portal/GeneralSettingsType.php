<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\GeneralSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->add('orig_deskpro_url', HiddenType::class, ['mapped' => false]) //unused, there for perf reasons on FE
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
            'data_class'         => GeneralSettings::class,
            'allow_extra_fields' => true,
        ]);
    }
}
