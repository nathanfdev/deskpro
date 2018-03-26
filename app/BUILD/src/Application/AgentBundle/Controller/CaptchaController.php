<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

class CaptchaController extends \Gregwar\CaptchaBundle\Controller\CaptchaController
{
    public function generateAction($key)
    {
        $this->preGenerate($key);

        return parent::generateCaptchaAction($key);
    }

    private function preGenerate($key)
    {
        $options      = $this->container->getParameter('gregwar_captcha.config');
        $session      = $this->get('session');
        $whitelistKey = $options['whitelist_key'];
        $keys         = $session->get($whitelistKey, []);
        if (!$keys || ($keys && !in_array($key, $keys))) {
            $session->set($whitelistKey, array_merge($keys, [$key]));
            $generator = $this->container->get('gregwar_captcha.generator');
            $generator->getCaptchaCode($options);
            $persistKeys = [
                'phrase',
                'width',
                'height',
                'distortion',
                'length',
                'quality',
                'background_color',
                'text_color',
            ];
            $persistOptions = [];
            foreach ($persistKeys as $persistKey) {
                $persistOptions[$persistKey] = $options[$persistKey];
            }
            $session->set($key, $persistOptions);
            $session->save();
        }
    }
}
