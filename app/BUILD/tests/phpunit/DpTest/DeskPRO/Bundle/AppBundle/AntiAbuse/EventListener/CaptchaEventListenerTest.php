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

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\AntiAbuse\EventListener;

use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseEvent;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\PasswordResetAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\RegistrationAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitCommentAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitFeedbackAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitTicketAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\TokenExchangeAbuseCheck;
use DpTest\PortalTestCase;
use Symfony\Component\HttpFoundation\Response;

class CaptchaEventListenerTest extends PortalTestCase
{
    protected function getRateLimitMaxAttempts($type)
    {
        return $this->get('settings_resolver')->getGlobalSettings()->get(
            'rate_limit.'.$type.'.limit'
        );
    }

    protected function getRateLimitResponse($type)
    {
        return $this->get('settings_resolver')->getGlobalSettings()->get(
            'rate_limit.'.$type.'.response'
        );
    }

    public function getRateLimitChecks()
    {
        $ip           = '127.0.0.1';
        $person_email = 'foo@bar.com';

        return [
            [AntiAbuse::ACTION_LOGIN, new LoginAbuseCheck($person_email, $ip)],
            [AntiAbuse::ACTION_REGISTER, new RegistrationAbuseCheck($person_email, $ip)],
            [AntiAbuse::ACTION_RESET_PASSWORD, new PasswordResetAbuseCheck($person_email, $ip)],
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
        $person = $this->get('test_factory.person')
                       ->createNewInvalidUser('foo@bar.com', 'Foo Bar', 'password123');

        $lessThanMaxAttempts = $this->getRateLimitMaxAttempts($type);
        for ($i = 0; $i < $lessThanMaxAttempts; ++$i) {
            // should not be recommending anything
            $this->get('anti_abuse')->check($event);

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
        $person = $this->get('test_factory.person')
                       ->createNewInvalidUser('foo@bar.com', 'Foo Bar', 'password123');

        $ip           = '255.50.70.10';
        $person_email = 'foo@bar.com';
        $event        = new LoginAbuseCheck($person_email, $ip);

        // this IP will be whitelisted in settings
        $this->get('settings_resolver')->setSetting(AntiAbuse::SETTING_IP_WHITELIST, json_encode([
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
