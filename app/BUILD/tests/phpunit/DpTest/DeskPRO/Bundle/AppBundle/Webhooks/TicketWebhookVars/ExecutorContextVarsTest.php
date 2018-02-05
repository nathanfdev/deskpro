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

namespace DpTest\DeskPRO\Bundle\AppBundle\Webhooks\WebhookExecutor;

use Application\DeskPRO\Tickets\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
use DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars\ExecutorContextEnv;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookHttpRequest;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookInvocation;
use DpTest\DeskProTestCase;

class ExecutorContextVarsTest extends DeskProTestCase
{
    public function testGetVariables()
    {
        $webhook    = new TicketWebhook();
        $request    = new WebhookHttpRequest('', [], '', []);
        $payload    = new \stdClass();
        $invocation = WebhookInvocation::fromRequestAndData($request, $payload);

        $context = new ExecutorContext();

        $actualWebhook = ExecutorContextEnv::getWebhook($context);
        $this->assertNull($actualWebhook);
        ExecutorContextEnv::setWebhook($context, $webhook);
        $actualWebhook = ExecutorContextEnv::getWebhook($context);
        $this->assertTrue($actualWebhook === $webhook);

        $actualRequest = ExecutorContextEnv::getWebhookRequest($context);
        $this->assertNull($actualRequest);
        ExecutorContextEnv::setWebhookRequest($context, $request);
        $actualRequest = ExecutorContextEnv::getWebhookRequest($context);
        $this->assertTrue($actualRequest === $request);

        $actualPayload = ExecutorContextEnv::getWebhookInvocation($context);
        $this->assertNull($actualPayload);
        ExecutorContextEnv::setWebhookInvocation($context, $invocation);
        $actualPayload = ExecutorContextEnv::getWebhookInvocation($context);
        $this->assertTrue($actualPayload === $invocation);
    }

    public function testGetWebhookRequestThrowsErrorWhenUnexpectedTypeRetrieved()
    {
        $context = new ExecutorContext();
        $context->getVars()->set('webhook_request', new \stdClass());

        $exception = null;
        try {
            ExecutorContextEnv::getWebhookRequest($context);
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
            ExecutorContextEnv::getWebhook($context);
        } catch (\DomainException $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);
    }

    public function testGetWebhookPayloadThrowsErrorWhenUnexpectedTypeRetrieved()
    {
        $context = new ExecutorContext();
        $context->getVars()->set('webhook_invocation', new \stdClass());

        $exception = null;
        try {
            ExecutorContextEnv::getWebhookInvocation($context);
        } catch (\DomainException $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);
    }
}
