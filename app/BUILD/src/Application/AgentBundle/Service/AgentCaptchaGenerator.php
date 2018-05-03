<?php

namespace Application\AgentBundle\Service;

use Gregwar\CaptchaBundle\Generator\CaptchaGenerator;

class AgentCaptchaGenerator extends CaptchaGenerator
{
    /**
     * {@inheritdoc}
     */
    public function getCaptchaCode(array &$options)
    {
        $this->builder->setPhrase($this->getPhrase($options));

        // Randomly execute garbage collection and returns the image filename
        if ($options['as_file']) {
            $this->imageFileHandler->collectGarbage();

            return $this->generate($options);
        }

        // Returns the image generation URL
        if ($options['as_url']) {
            return $this->router->generate('gregwar_captcha.agent.generate_captcha',
                ['key' => $options['session_key'], 'n' => md5(microtime(true).mt_rand())]);
        }

        return 'data:image/jpeg;base64,'.base64_encode($this->generate($options));
    }
}
