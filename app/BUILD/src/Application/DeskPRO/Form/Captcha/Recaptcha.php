<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Form\Captcha;

use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Strings;

class Recaptcha extends CaptchaAbstract
{
    const RECAPTCHA_VERIFY_URL = 'http://www.google.com/recaptcha/api/verify';

    /**
     * @var string
     */
    protected $public_key = null;

    /**
     * @var string
     */
    protected $private_key = null;

    public function init()
    {
        $this->setOptions([
            'template' => 'DeskPRO:Common:recaptcha.html.twig',
        ]);

        $this->public_key  = $this->getOptionOrSetting('public_key', 'core.recaptcha_public_key');
        $this->private_key = $this->getOptionOrSetting('private_key', 'core.recaptcha_private_key');
    }

    public function getHtml()
    {
        $tpl  = $this->getOption('template');
        $vars = [
            'public_key' => $this->public_key,
        ];

        return $this->getTemplating()->render($tpl, $vars);
    }

    public function validate()
    {
        $challenge = $this->getRequest()->request->get('recaptcha_challenge_field');
        $response  = $this->getRequest()->request->get('recaptcha_response_field');
        $remote_ip = @$_SERVER['HTTP_CLIENT_IP'];

        if (!$challenge || !$response || !$remote_ip) {
            return false;
        }

        $client = new \Zend\Http\Client(self::RECAPTCHA_VERIFY_URL);
        $client->setMethod(\Zend\Http\Request::METHOD_POST);
        $client->getRequest()->getPost()->set('privatekey', $this->private_key);
        $client->getRequest()->getPost()->set('remoteip', $remote_ip);
        $client->getRequest()->getPost()->set('challenge', $challenge);
        $client->getRequest()->getPost()->set('response', $response);

        try {
            $r_response = $client->send();
            $r_body     = $r_response->getBody();

            if (!$r_response->isOk()) {
                $r_body = 'true'; //fallback on OK when it fails
            }
        } catch (\Exception $e) {
            KernelErrorHandler::logException($e, false);
            $r_body = 'true'; //fallback on OK when it fails
        }

        $line = trim(Strings::getFirstLine($r_body));

        return $line == 'true';
    }
}
