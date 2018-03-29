<?php

namespace DpTest\DeskPRO\Bundle\AppBundle\AntiAbuse\EventListener;

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseEvent;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitCommentAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitFeedbackAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitTicketAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\TokenExchangeAbuseCheck;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\RateLimitOptionsGroup;
use DpTest\PortalTestCase;

class CaptchaEventListenerTest extends PortalTestCase
{
    protected function getRateLimitMaxAttempts($type)
    {
        if ($type === AntiAbuse::ACTION_LOGIN) {
            $key = 'rate_limit.'.$type.'.guest.limit';
        } else {
            $key = 'rate_limit.'.$type.'.limit';
        }

        return $this->get('settings_resolver')->getGlobalSettings()->get($key);
    }

    protected function getRateLimitResponse($type)
    {
        if ($type === AntiAbuse::ACTION_LOGIN) {
            $key = 'rate_limit.'.$type.'.guest.response';
        } else {
            $key = 'rate_limit.'.$type.'.response';
        }

        return $this->get('settings_resolver')->getGlobalSettings()->get($key);
    }

    public function getRateLimitChecks()
    {
        $ip           = '127.0.0.1';
        $person_email = 'foo@bar.com';

        return [
            [AntiAbuse::ACTION_LOGIN, new LoginAbuseCheck($person_email, $ip)],
            [AntiAbuse::ACTION_SUBMIT_COMMENT, new SubmitCommentAbuseCheck($person_email, $ip)],
            [AntiAbuse::ACTION_SUBMIT_FEEDBACK, new SubmitFeedbackAbuseCheck($person_email, $ip)],
            [AntiAbuse::ACTION_SUBMIT_TICKET, new SubmitTicketAbuseCheck($person_email, $ip)],
            [AntiAbuse::ACTION_TOKEN_EXCHANGE, new TokenExchangeAbuseCheck($person_email, $ip)],
        ];
    }

    /**
     * @dataProvider getRateLimitChecks
     */
    public function testCaptchaRecommendationForAllCheckEvents($type, AntiAbuseEvent $event)
    {
        $this->installDataSet('fresh', true);
        $this->get('test_factory.person')->createNewInvalidUser('foo@bar.com', 'Foo Bar', 'password123');

        if ($type === AntiAbuse::ACTION_LOGIN) {
            $settingName = AntiAbuse::KEY.'.'.$type.'.guest.response';
        } else {
            $settingName = AntiAbuse::KEY.'.'.$type.'.response';
        }

        $this->getEntityManager()->getRepository(Setting::class)->updateSetting($settingName, RateLimitOptionsGroup::RESPONSE_CAPTCHA);

        $lessThanMaxAttempts = $this->getRateLimitMaxAttempts($type);
        for ($i = 0; $i < $lessThanMaxAttempts; ++$i) {
            // should not be recommending anything
            $this->get('anti_abuse')->check($event);
            if ($event->getType() === AntiAbuse::ACTION_LOGIN) {
                //rate limit for login is little bit hacky
                $this->get('anti_abuse')->saveRateLimit($event);
            }

            // no recommendations should be made (we are always under or equal limit here)
            $this->assertFalse($event->isCaptchaRecommended());
        }

        // on the next check (check only), we will know we hit the limit, and there should be a captcha recommended
        $this->get('anti_abuse')->check($event);
        $event->markAsCheckOnly();
        $this->assertTrue($event->isCaptchaRecommended(), 'rate limit applies if IP is NOT whitelisted');
    }

    public function testRateLimitWhitelistWontShowCaptcha()
    {
        $this->installDataSet('fresh', true);

        $ip           = '255.50.70.10';
        $person_email = 'foo@bar.com';
        $event        = new LoginAbuseCheck($person_email, $ip);

        $this->get('test_factory.person')->createNewInvalidUser($person_email, 'Foo Bar', 'password123');

        // this IP will be whitelisted in settings
        $this->getEntityManager()->getRepository(Setting::class)->updateSetting(AntiAbuse::SETTING_IP_WHITELIST, json_encode([
            $ip,
        ]));

        $lessThanMaxAttempts = $this->getRateLimitMaxAttempts(AntiAbuse::ACTION_LOGIN) - 1;
        for ($i = 0; $i < $lessThanMaxAttempts; ++$i) {
            // should not be recommending anything
            $this->get('anti_abuse')->check($event);

            // no recommendations should be made (we are always under limit here)
            $this->assertFalse($event->isCaptchaRecommended());
        }

        // on the next check, we will hit the limit, and there should be a captcha recommended
        $this->get('anti_abuse')->check($event);
        $this->assertFalse($event->isCaptchaRecommended(), 'rate limit does not apply if IP is whitelisted');
    }
}
