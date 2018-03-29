<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings\AntiAbuse;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\Captcha\CaptchaAntiAbuseType;
use DeskPRO\Bundle\AppBundle\Settings\CaptchaSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\CaptchaAntiAbuseSettings;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class CaptchaAntiAbuseSetupController.
 *
 * @ApiModes("all")
 * @Rest\Route("/settings/anti_abuse/captcha")
 * @ApiDoc(target="all", output="DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\CaptchaAntiAbuseSettings")
 * @ApiDoc(
 *     target="putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Settings\AntiAbuse\Captcha\CaptchaAntiAbuseType"
 *     }
 * )
 */
class CaptchaAntiAbuseSetupController extends AbstractAntiAbuseSetupController
{
    protected static $resolver = 'captcha_settings_resolver';
    protected static $model    = CaptchaAntiAbuseType::class;

    /**
     * {@inheritdoc}
     *
     * @param CaptchaAntiAbuseSettings $model
     */
    protected function persistModel($model)
    {
        $settings_repository = $this->getSettingRepository();
        $settings_repository
            ->updateSetting(CaptchaSettingsResolver::USE_RECAPTCHA, $model->getUseRecaptcha2())
            ->updateSetting(CaptchaSettingsResolver::RECAPTCHA_SITE_KEY, $model->getRecaptcha2SiteKey())
            ->updateSetting(CaptchaSettingsResolver::RECAPTCHA_SECRET_KEY, $model->getRecaptcha2SecretKey())
            ->updateSetting(CaptchaSettingsResolver::TICKETS, $model->getTickets())
            ->updateSetting(CaptchaSettingsResolver::COMMENTS, $model->getComments())
            ->updateSetting(CaptchaSettingsResolver::FEEDBACK, $model->getFeedback())
            ->updateSetting(CaptchaSettingsResolver::REGISTER, $model->getRegister())
            ->updateSetting(CaptchaSettingsResolver::SHARING, $model->getSharing())
        ;
    }
}
