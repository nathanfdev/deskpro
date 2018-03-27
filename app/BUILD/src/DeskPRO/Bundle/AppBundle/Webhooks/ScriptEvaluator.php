<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

interface ScriptEvaluator
{
    /**
     * @param mixed $script
     *
     * @return bool
     */
    public function canEvaluate($script);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param WebhookInvocation $invocation
     * @param $script
     *
     * @return mixed
     */
    public function evaluate(WebhookInvocation $invocation, $script);
}
