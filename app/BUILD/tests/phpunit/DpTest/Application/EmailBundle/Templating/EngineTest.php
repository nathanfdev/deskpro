<?php

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
