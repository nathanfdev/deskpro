<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

class TwigScriptEvaluator implements ScriptEvaluator
{
    /**
     * @param string $script
     *
     * @return string
     */
    private function prepareScript($script)
    {
        if (substr($script, 0, strlen('twig:')) === 'twig:') {
            return substr($script, strlen('twig:'));
        }

        return $script;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'twig';
    }

    /**
     * @param WebhookInvocation $invocation
     * @param string            $script
     *
     * @throws WebhookException
     *
     * @return mixed
     */
    public function evaluate(WebhookInvocation $invocation, $script)
    {
        $preparedScript = $this->prepareScript($script);
        $loader         = new \Twig_Loader_Array(['script.html' => $preparedScript]);
        $twig           = new \Twig_Environment($loader);

        try {
            // hold any sandboxing for now
            // $sandbox = new \Twig_Extension_Sandbox($policy, true);
            $context = $invocation->toPropertyMap();
            return $twig->render('script.html', ['webhook' => $context]);
        } catch (\Twig_Error $e) {
            $msg = 'Error evaluating webhook script';
            throw new WebhookException($msg, 0, $e);
        }
    }

    /**
     * @param string $script
     *
     * @return bool
     */
    public function canEvaluate($script)
    {
        if (!is_string($script)) {
            return false;
        }

        if (substr($script, 0, strlen('twig:')) !== 'twig:') {
            return false;
        }

        $preparedScript = $this->prepareScript($script);
        $loader         = new \Twig_Loader_Array(['script.html' => $preparedScript]);
        $twig           = new \Twig_Environment($loader);
        try {
            $nodeTree = $twig->parse($twig->tokenize($script, 'script.html'));
            $twig->compile($nodeTree);
        } catch (\Twig_Error $e) {
            return false;
        }

        return true;
    }
}
