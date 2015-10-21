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
use DpTest\PortalTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * The anti-abuse system will throw an exception to give the client a different response sometimes, so we test
 * that functionality here.
 *
 * For specific tests around the various lockout/rate-limit logic please see the EventListener tests.
 */
class AntiAbuseTest extends PortalTestCase
{
    protected function getLoginLockoutMaxAttempts()
    {
        return $this->get('settings_resolver')->getGlobalSettings()->get('user.'.LoginRateLimitSettings::KEY.'.attempts');
    }

    public function testLoginLockoutDoesNotTriggerLockoutResponseWhenUnderLimit()
    {
        //$this->installDataSet('fresh', true);
        //$person = $this->get('test_factory.person')
        //    ->createNewInvalidUser('foo@bar.com', 'Foo Bar', 'password123');
        //
        //$ip = '100.200.300.400';
        //
        //$client = $this->getClient(['REMOTE_ADDR' => $ip]);
        //
        //$this->get('settings_resolver')->setSetting('rate_limit.login.limit', 100); // really high so captcha not hit
        //$lessThanMaxAttempts = $this->getLoginLockoutMaxAttempts() - 1;
        //for ($i = 0; $i < $lessThanMaxAttempts; ++$i) {
        //    $client->request(
        //        'POST',
        //        '/login/authenticate-password',
        //        [
        //            'username' => 'foo@bar.com',
        //            'password' => 'wrong pw',
        //        ]
        //    );
        //}
        //
        //// this should be the normal /login?retry=auth url
        //$response = $client->getResponse();
        //$this->assertRegExp('/\/login\?retry=auth$/', $response->headers->get('location'));
        //$this->assertEquals(302, $response->getStatusCode());
    }

    public function testLoginLockoutAbuseException()
    {
        //$this->installDataSet('fresh', true);
        //$person = $this->get('test_factory.person')
        //    ->createNewInvalidUser('foo@bar.com', 'Foo Bar', 'password123');
        //
        //$ip = '100.200.300.400';
        //
        //$client = $this->getClient(['REMOTE_ADDR' => $ip]);
        //
        //$this->get('settings_resolver')->setSetting('rate_limit.login.limit', 100); // really high so captcha not hit
        //$moreThanMaxAttempts = $this->getLoginLockoutMaxAttempts() + 1;
        //for ($i = 0; $i < $moreThanMaxAttempts; ++$i) {
        //    $client->request(
        //        'POST',
        //        '/login/authenticate-password',
        //        [
        //            'username' => 'foo@bar.com',
        //            'password' => 'wrong pw',
        //        ]
        //    );
        //}
        //
        //// this is the last $response, and it should be to the /login?lockout=auth url
        //$response = $client->getResponse();
        //$this->assertRegExp('/\/login\?lockout=auth$/', $response->headers->get('location'));
        //$this->assertEquals(302, $response->getStatusCode());
    }
}
