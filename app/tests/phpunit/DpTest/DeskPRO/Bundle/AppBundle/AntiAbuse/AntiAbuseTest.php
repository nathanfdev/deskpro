<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\AntiAbuse;

use Application\DeskPRO\Settings\LoginRateLimitSettings;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Exception\AntiAbuseException;
use DpTest\PortalTestCase;
use Symfony\Component\HttpFoundation\Response;

class AntiAbuseTest extends PortalTestCase
{
    protected function getLoginLockoutMaxAttempts()
    {
        return $this->get('settings_resolver')->getGlobalSettings()->get('user.'.LoginRateLimitSettings::KEY.'.attempts');
    }

    public function testLoginLockoutDoesNotTriggerWhenUnderLimit()
    {
        $this->installDataSet('fresh', true);
        $person = $this->get('test_factory.person')
            ->createNewInvalidUser('foo@bar.com', 'Foo Bar', 'password123');

        $ip = '100.200.300.400';

        $client = $this->getClient(['REMOTE_ADDR' => $ip]);

        $lessThanMaxAttempts = $this->getLoginLockoutMaxAttempts() - 1;
        for ($i = 0; $i < $lessThanMaxAttempts; ++$i) {
            $client->request(
                'POST',
                '/login/authenticate-password',
                [
                    'username' => 'foo@bar.com',
                    'password' => 'wrong pw',
                ]
            );
        }

        // this should be the normal /login?retry=auth url
        $response = $client->getResponse();
        $this->assertRegExp('/\/login\?retry=auth$/', $response->headers->get('location'));
        $this->assertEquals(302, $response->getStatusCode());

        // should not be recommending anything (the limit is 20)
        $event = new LoginAbuseCheck($person, $ip);
        $event->markAsCheckOnly(); // checking state only
        $this->get('anti_abuse')->check($event);
        $this->assertFalse($event->isCaptchaRecommended());
        $this->assertFalse($event->isLockoutRecommended());
        $this->assertFalse($event->isResponseRecommended());
        $this->assertNull($event->getRecommendedResponse());
    }

    public function testLoginLockoutAbuseException()
    {
        $this->installDataSet('fresh', true);
        $person = $this->get('test_factory.person')
            ->createNewInvalidUser('foo@bar.com', 'Foo Bar', 'password123');

        $ip = '100.200.300.400';

        $client = $this->getClient(['REMOTE_ADDR' => $ip]);

        $moreThanMaxAttempts = $this->getLoginLockoutMaxAttempts() + 1;
        for ($i = 0; $i < $moreThanMaxAttempts; ++$i) {
            $client->request(
                'POST',
                '/login/authenticate-password',
                [
                    'username' => 'foo@bar.com',
                    'password' => 'wrong pw',
                ]
            );
        }

        // this is the last $response, and it should be to the /login?lockout=auth url
        $response = $client->getResponse();
        $this->assertRegExp('/\/login\?lockout=auth$/', $response->headers->get('location'));
        $this->assertEquals(302, $response->getStatusCode());

        // should now be recommending lockout (>20 attemps so quickly)
        $event = new LoginAbuseCheck($person, $ip);
        $event->markAsCheckOnly(); // checking state only

        // this is expected to throw an AntiAbuseException, which tells the kernel to return the Response object in the event
        try {
            $this->get('anti_abuse')->check($event);
        } catch (AntiAbuseException $exception) {
            $event = $exception->getAntiAbuseEvent();
            $this->assertFalse($event->isCaptchaRecommended());
            $this->assertTrue($event->isLockoutRecommended());
            $this->assertTrue($event->isResponseRecommended());
            $this->assertInstanceOf(Response::class, $event->getRecommendedResponse());
        }
    }
}
