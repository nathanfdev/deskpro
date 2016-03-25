<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Settings;

use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\CaptchaAntiAbuseSettings;

/**
 * Class CaptchaSettingsResolver.
 */
class CaptchaSettingsResolver implements AntiAbuseSettingsAwareInterface
{
    const TICKETS  = 'user.captcha.tickets';
    const COMMENTS = 'user.captcha.comments';
    const FEEDBACK = 'user.captcha.feedback';
    const REGISTER = 'user.captcha.register';

    const USE_RECAPTCHA        = 'core.use_recaptcha2';
    const RECAPTCHA_SITE_KEY   = 'core.recaptcha2_site_key';
    const RECAPTCHA_SECRET_KEY = 'core.recaptcha2_secret_key';

    /**
     * @var BrandAwareSettingsResolver
     */
    protected $settingsResolver;

    /**
     * Constructor.
     *
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(BrandAwareSettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @return CaptchaAntiAbuseSettings
     */
    public function getAntiAbuseSettings()
    {
        $model = new CaptchaAntiAbuseSettings();
        $model
            ->setUseReCaptcha2($this->getSetting(self::USE_RECAPTCHA))
            ->setReCaptcha2SiteKey($this->getSetting(self::RECAPTCHA_SITE_KEY))
            ->setTickets($this->getSetting(self::TICKETS))
            ->setComments($this->getSetting(self::COMMENTS))
            ->setFeedback($this->getSetting(self::FEEDBACK))
            ->setRegister($this->getSetting(self::REGISTER))
        ;

        return $model;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    private function getSetting($name)
    {
        return $this->settingsResolver->getSetting($name);
    }
}
