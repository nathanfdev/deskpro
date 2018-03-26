<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars;

use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookInvocation;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookRequest;

class ExecutorContextEnv
{
    /**
     * @param ExecutorContextInterface $context
     * @param TicketWebhook            $webhook
     */
    public static function setWebhook(ExecutorContextInterface $context, TicketWebhook $webhook)
    {
        $context->getVars()->set('webhook', $webhook);
    }

    /**
     * @param ExecutorContextInterface $context
     *
     * @return TicketWebhook|null
     */
    public static function getWebhook(ExecutorContextInterface $context)
    {
        $varName = 'webhook';
        $webhook = $context->getVars()->get($varName);
        if (!is_null($webhook) && !$webhook instanceof TicketWebhook) {
            $msg = sprintf('unexpected type for webhook variable named: %s', $varName);
            throw new \DomainException($msg);
        }

        return $webhook;
    }

    public static function setWebhookRequest(ExecutorContextInterface $context, WebhookRequest $request)
    {
        $context->getVars()->set('webhook_request', $request);
    }

    /**
     * @param ExecutorContextInterface $context
     *
     * @return TicketWebhook|null
     */
    public static function getWebhookRequest(ExecutorContextInterface $context)
    {
        $varName = 'webhook_request';
        $request = $context->getVars()->get($varName);
        if (!is_null($request) && !$request instanceof WebhookRequest) {
            $msg = sprintf('unexpected type for webhook variable named: %s', $varName);
            throw new \DomainException($msg);
        }

        return $request;
    }

    public static function setWebhookInvocation( ExecutorContextInterface $context, WebhookInvocation $payload)
    {
        $context->getVars()->set('webhook_invocation', $payload);
    }

    /**
     * Returns a list of all the webhook vars which are available as trigger vars
     *
     * @param ExecutorContextInterface $context
     * @return array
     */
    public static function getTriggerVars( ExecutorContextInterface $context)
    {
        return [
            'webhook' => ExecutorContextEnv::getWebhookInvocation($context)
        ];
    }

    /**
     * @param ExecutorContextInterface $context
     *
     * @return WebhookInvocation
     */
    public static function getWebhookInvocation( ExecutorContextInterface $context)
    {
        $varName = 'webhook_invocation';
        $request = $context->getVars()->get($varName);
        if (!is_null($request) && !$request instanceof WebhookInvocation) {
            $msg = sprintf('unexpected type for webhook variable named: %s', $varName);
            throw new \DomainException($msg);
        }

        return $request;
    }
}
