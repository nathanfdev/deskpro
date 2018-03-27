<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\Captcha;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\CaptchaAntiAbuseSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CaptchaAntiAbuseType.
 */
class CaptchaAntiAbuseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('use_recaptcha2', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('recaptcha2_site_key', TextType::class, [
                'required' => false,
            ])
            ->add('recaptcha2_secret_key', TextType::class, [
                'required' => false,
            ])
            ->add('tickets', CaptchaSettingChoiceType::class, [
                'required' => false,
            ])
            ->add('comments', CaptchaSettingChoiceType::class, [
                'required' => false,
            ])
            ->add('feedback', CaptchaSettingChoiceType::class, [
                'required' => false,
            ])
            ->add('register', CaptchaSettingChoiceType::class, [
                'required' => false,
            ])
            ->add('sharing', CaptchaSettingChoiceType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CaptchaAntiAbuseSettings::class,
        ]);
    }
}
