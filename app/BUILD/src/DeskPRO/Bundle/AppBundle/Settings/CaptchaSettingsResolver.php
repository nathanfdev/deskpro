<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\CaptchaAntiAbuseSettings;

/**
 * Class CaptchaSettingsResolver.
 */
class CaptchaSettingsResolver extends AbstractBrandAwareSettingsResolver implements AntiAbuseSettingsAwareInterface
{
    const TICKETS  = 'user.captcha.tickets';
    const COMMENTS = 'user.captcha.comments';
    const FEEDBACK = 'user.captcha.feedback';
    const REGISTER = 'user.captcha.register';
    const SHARING  = 'user.captcha.sharing';

    const USE_RECAPTCHA        = 'core.use_recaptcha2';
    const RECAPTCHA_SITE_KEY   = 'core.recaptcha2_site_key';
    const RECAPTCHA_SECRET_KEY = 'core.recaptcha2_secret_key';

    /**
     * {@inheritdoc}
     *
     * @return CaptchaAntiAbuseSettings
     */
    public function getAntiAbuseSettings()
    {
        $model = new CaptchaAntiAbuseSettings();
        $model
            ->setUseRecaptcha2($this->getSetting(self::USE_RECAPTCHA))
            ->setRecaptcha2SiteKey($this->getSetting(self::RECAPTCHA_SITE_KEY))
            ->setRecaptcha2SecretKey($this->getSetting(self::RECAPTCHA_SECRET_KEY))
            ->setTickets($this->getSetting(self::TICKETS))
            ->setComments($this->getSetting(self::COMMENTS))
            ->setFeedback($this->getSetting(self::FEEDBACK))
            ->setRegister($this->getSetting(self::REGISTER))
            ->setSharing($this->getSetting(self::SHARING))
        ;

        return $model;
    }
}
