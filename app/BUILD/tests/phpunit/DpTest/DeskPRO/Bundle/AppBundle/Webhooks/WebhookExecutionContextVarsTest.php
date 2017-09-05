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

namespace DpTest\DeskPRO\Bundle\AppBundle\Webhooks;

use Application\DeskPRO\Tickets\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookExecutionContextVars;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookRequest;
use DpTest\DeskProTestCase;

class WebhookExecutionContextVarsTest extends DeskProTestCase
{
    public function testGetVariables()
    {
        $webhook = new TicketWebhook();
        $request = new WebhookRequest([], '', [], '');
        $payload = new \stdClass();

        $context = new ExecutorContext();

        $actualWebhook = WebhookExecutionContextVars::getWebhook($context);
        $this->assertNull($actualWebhook);
        WebhookExecutionContextVars::setWebhook($context, $webhook);
        $actualWebhook = WebhookExecutionContextVars::getWebhook($context);
        $this->assertTrue($actualWebhook === $webhook);


        $actualRequest = WebhookExecutionContextVars::getWebhookRequest($context);
        $this->assertNull($actualRequest);
        $actualRequest = WebhookExecutionContextVars::getWebhookRequest($context);
        $this->assertTrue($actualRequest === $request);


        $actualPayload = WebhookExecutionContextVars::getWebhookPayload($context);
        $this->assertNull($actualPayload);
        WebhookExecutionContextVars::setWebhookPayload($context, $payload);
        $actualPayload = WebhookExecutionContextVars::getWebhookPayload($context);
        $this->assertTrue($actualPayload === $payload);
    }

    public function testGetWebhookRequestThrowsErrorWhenUnexpectedTypeRetrieved()
    {
        $context = new ExecutorContext();
        $context->getVars()->set('webhook_request', new \stdClass());

        $exception = null;
        try {
            WebhookExecutionContextVars::getWebhookRequest($context);
        } catch (\DomainException $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);
    }

    public function testGetWebhookThrowsErrorWhenUnexpectedTypeRetrieved()
    {
        $context = new ExecutorContext();
        $context->getVars()->set('webhook', new \stdClass());

        $exception = null;
        try {
            WebhookExecutionContextVars::getWebhook($context);
        } catch (\DomainException $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);
    }

}
