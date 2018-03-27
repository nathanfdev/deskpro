<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\Captcha;

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\CaptchaAntiAbuseSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CaptchaSettingChoiceType.
 */
class CaptchaSettingChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices_as_values' => true,
            'choices'           => [
                CaptchaAntiAbuseSettings::TYPE_BASED_RATE_LIMITS,
                CaptchaAntiAbuseSettings::TYPE_GUESTS,
                CaptchaAntiAbuseSettings::TYPE_EVERYONE,
            ],
        ]);
    }
}
