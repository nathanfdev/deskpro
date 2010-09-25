<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Field;

use \Symfony\Component\EventDispatcher\EventDispatcher;
use \Symfony\Component\EventDispatcher\Event;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A simple CSRF protection that uses static codes (ie that arent saved to a datasource).
 * Codes are unguessable, but should expire.
 */
class FormStaticCsrf
{
	/**
	 * The name of the form item that contains the csrf token
	 * @var string
	 */
	protected $form_name = '__security_token';

	/**
	 * The server secret that never changes
	 * @var string
	 */
	protected $secret = '';

	/**
	 * The user secret that changes depending on the user.
	 * @var string
	 */
	protected $user_secret = '';

	/**
	 * How long until a token expires (in seconds).
	 * @var int
	 */
	protected $timeout = 18000;


	/**
	 * @param string $secret The secret token only the server knows. This should never change.
	 * @param string $user_secret The secret token that only the current user should know. A session ID for example
	 */
	public function __construct($secret, $user_secret)
	{
		$this->secret = $secret;
		$this->user_secret = $user_secret;
	}


	/**
	 * Register this listener on the form dispatcher
	 * 
	 * @param EventDispatcher $dispatcher
	 */
	public function register(EventDispatcher $dispatcher)
	{
		$dispatcher->connect('orb.form.render_form_tag', array($this, 'handleRenderFormTag'));
		$dispatcher->connect('orb.form.init', array($this, 'handleFormInit'));
	}


	
	/**
	 * Adds a hidden input field to the form automatically
	 *
	 * @param Event $event
	 * @param string $html
	 */
	public function handleRenderFormTag(Event $event, $html)
	{
		$hidden = '<input type="hidden" name="' . $this->getFormFieldName() . '" value="' . $this->getNewToken() . '" />';

		$html .= $hidden;

		return $html;
	}


	
	/**
	 * Adds a new validator to validate the CSRF token when validating the form
	 *
	 * @param Event $event
	 */
	public function handleFormInit(Event $event)
	{
		$fn = function(FormStaticCsrf $handler) {
			if ($handler->checkRequestToken()) {
				return false; // no error
			}

			return 'invalid_csrf_token';
		};
		$validator = new \Orb\Validator\Callback($fn, array($this, \Orb\Validator\Callback::ARG_PLACEHOLDER));

		$form = $event->getSubject();
		$form->addValidator($validator, true);
	}


	
	/**
	 * Check POST and GET for the token field, and if it was supplied,
	 * verify its correct.
	 *
	 * @return bool
	 */
	public function checkRequestToken()
	{
		if (isset($_POST[$this->getFormFieldName()])) {
			$token = $_POST[$this->getFormFieldName()];
		} elseif (isset($_GET[$this->getFormFieldName()])) {
			$token = $_GET[$this->getFormFieldName()];
		} else {
			return false;
		}

		return $this->checkToken($token);
	}



	/**
	 * Generate a new random security token
	 *
	 * @return string
	 */
	public function getNewToken()
	{
		return Util::generateStaticSecurityToken($this->secret . $this->user_secret, $this->timeout);
	}

	

	/**
	 * Check to see if a security token is valid
	 *
	 * @param string $token
	 * @return bool
	 */
	public function checkToken($token)
	{
		return Util::checkStaticSecurityToken($token, $this->secret . $this->user_secret);
	}


	
	/**
	 * Set what the hidden form field is called.
	 * s
	 * @param string $form_name
	 */
	public function setFormFieldName($form_name)
	{
		$this->form_name = $form_anme;
	}


	
	/**
	 * Get the form field name
	 *
	 * @return string
	 */
	public function getFormFieldName()
	{
		return $this->form_name;
	}


	
	/**
	 * How long should a token be valid for?
	 *
	 * @param string $timeout
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;
	}


	
	/**
	 * Get the timeout
	 *
	 * @return int
	 */
	public function getTimeout()
	{
		return $this->timeout;
	}
}