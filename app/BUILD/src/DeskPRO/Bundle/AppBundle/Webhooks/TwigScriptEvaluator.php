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
