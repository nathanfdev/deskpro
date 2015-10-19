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
namespace DpTest\DeskPRO\Bundle\PortalBundle\Form\Captcha;

use DpTest\PortalTestCase;

class CaptchaDeciderTest extends PortalTestCase
{
    public function testRegistrationFormCaptcha()
    {
        /** @var \DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider $captcha_decider */
        $captcha_decider = $this->get('captcha_decider');

        $this->get('request_stack')->push();

        $decision = $captcha_decider->shouldRequireRegistrationCaptchaForCurrentPerson();

        $this->assertFalse($decision, 'no catpcha is required before a rate limit');

        /** @var \DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse $anti_abuse */
        $anti_abuse = $this->get('anti_abuse');
    }
}
