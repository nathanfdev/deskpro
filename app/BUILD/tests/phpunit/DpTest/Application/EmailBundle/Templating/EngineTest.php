<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DpTest\Application\EmailBundle\Templating;

use DpTest\PortalTestCase;

/**
 * Very simple tests that just make sure the templating.email.engine is
 * not throwing exceptions when loading/trying to render templates.
 */
class EngineTest extends PortalTestCase
{
    public function testEngineRendersBasicEmailBundleTemplate()
    {
        $this->installDataSet('fresh');
        $this->assertNotEmpty(
            $this->getTemplating()->render('DeskPRO:emails_user:reset-password.html.twig', []),
            'EmailBundle templates load and can be rendered'
        );
    }

    public function testEngineRendersBasicDeskproEmailTemplate()
    {
        $this->installDataSet('fresh');
        $this->assertNotEmpty(
            $this->getTemplating()->render('DeskPRO:emails_agent:agent-welcome-usersource.html.twig', []),
            'DeskPRO email templates load and can be rendered'
        );
    }

    /**
     * @return \Application\EmailBundle\Templating\Engine
     */
    protected function getTemplating()
    {
        return $this->get('templating.email.engine');
    }
}
