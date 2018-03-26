<?php

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
