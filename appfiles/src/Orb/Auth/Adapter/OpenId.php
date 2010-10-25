<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Auth\Adapter;

use \Orb\Util\Arrays;

use \Orb\Auth\Adapter\SessionStateInterface;
use \Orb\Auth\Adapter\CallbackInterface;
use \Orb\Auth\StateHandler\StateHandlerInterface;
use \Orb\Auth\Result;

class OpenId extends AbstractCallbackAdatper
{
	protected $openid_identifier = '';



	/**
	 * Sets the data got from a form
	 *
	 * @param string $url The URL
	 */
	public function setFormData(array $form_data)
	{
		if (!empty($form_data['openid_identifier'])) {
			$this->openid_identifier = $form_data['openid_identifier'];
		}
	}



	/**
	 * Initialize the auth process by setting state, and returning a redirect result.
	 *
	 * @return Orb\Auth\Result
	 */
	protected function authenticateInitialize(StateHandlerInterface $state)
	{
		$openid = new \LightOpenID();
		$openid->identity = $this->openid_identifier;
		$openid->returnUrl = $this->getCallbackUrl();
		$openid->optional = array(
			'namePerson/friendly', 'contact/email', 'namePerson',
			'birthDate', 'person/gender', 'contact/country/home',
			'pref/language', 'pref/timezone'
		);

		try {
			$result = new Result(Result::REQUIRES_REDIRECT, null, array(Result::MSG_REDIRECT => $openid->authUrl()));
			return $result;
		} catch (\ErrorException $e) {
			$result = new Result(Result::FAILURE_EXCEPTION, null, array(Result::MSG_EXCEPTION => $e));
			return $result;
		}		
	}


	protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
	{
		$openid = new \LightOpenID();

		if (!$openid->validate()) {
			return new Result(Result::FAILURE, null, array('error_code' => 'invalid_validate', 'error_message' => 'Could not validate'));
		}

		$attributes = $openid->getAttributes();
		$userinfo = array(
			'nickname'  => !empty($attributes['namePerson/friendly'])   ? $attributes['namePerson/friendly']    : null,
			'email'     => !empty($attributes['email'])                 ? $attributes['email']                  : null,
			'fullname'  => !empty($attributes['namePerson'])            ? $attributes['namePerson']             : null,
			'birthday'  => !empty($attributes['birthDate'])             ? $attributes['birthDate']              : null,
			'gender'    => !empty($attributes['person/gender'])         ? $attributes['person/gender']          : null,
			'country'   => !empty($attributes['contact/country/home'])  ? $attributes['contact/country/home']   : null,
			'language'  => !empty($attributes['pref/language'])         ? $attributes['pref/language']          : null,
			'timezone'  => !empty($attributes['pref/timezone'])         ? $attributes['pref/timezone']          : null,
		);
		$userinfo = Arrays::removeFalsey($userinfo);

		$identity = new \Orb\Auth\Identity($openid->identity, $userinfo);

		$result = new Result(Result::SUCCESS, $identity);

		return $result;
	}
}