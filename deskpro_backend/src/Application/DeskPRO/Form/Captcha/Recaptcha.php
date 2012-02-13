<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Form\Captcha;

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
		$this->setOptions(array(
			'template' => 'DeskPRO:Common:recaptcha.html.twig'
		));

		$this->public_key  = $this->getOptionOrSetting('public_key', 'core.recaptcha_public_key');
		$this->private_key = $this->getOptionOrSetting('private_key', 'core.recaptcha_private_key');
	}

	public function getHtml()
	{
		$tpl = $this->getOption('template');
		$vars = array(
			'public_key' => $this->public_key
		);

		return $this->getTemplating()->render($tpl, $vars);
	}

	public function validate()
	{
		$challenge = $this->getRequest()->request->get('recaptcha_challenge_field');
		$response  = $this->getRequest()->request->get('recaptcha_response_field');
		$remote_ip = $this->getRequest()->getClientIp();

		if (!$challenge || !$response || !$remote_ip) {
			return false;
		}

		$client = new \Zend\Http\Client(self::RECAPTCHA_VERIFY_URL);
		$client->setMethod(\Zend\Http\Request::METHOD_POST);
		$client->getRequest()->post()->set('privatekey', $this->private_key);
		$client->getRequest()->post()->set('remoteip', $remote_ip);
		$client->getRequest()->post()->set('challenge', $challenge);
		$client->getRequest()->post()->set('response', $response);

		$r_response = $client->send();
		$r_body = $r_response->getBody();

		$line = trim(Strings::getFirstLine($r_body));

		return ($line == 'true');
	}
}
