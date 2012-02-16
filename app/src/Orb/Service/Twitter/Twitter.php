<?php

namespace Orb\Service\Twitter;

use \Application\DeskPRO\App;

class Twitter extends \Zend\Service\Twitter
{
	/**
	 * @param \Zend\Oauth\Token\Access $accessToken
	 * @param \Zend\Oauth\Consuner $consumer (optional)
	 * @return \Zend\Service\Twitter
	 */
	static public function getTwitterService(\Zend\Oauth\Token\Access $accessToken, \Zend\Oauth\Consumer $consumer = null)
	{
		if (null === $consumer) {
			$consumer = Oauth::getConsumer();
		}

		return new self(array(
			'accessToken' => $accessToken
		), $consumer);
	}
	/**
	 * Show a single status
	 *
	 * @param  int $id Id of status to show
	 * @param array $params (optional)
	 * @return \Zend\Rest\Client_Result
	 * @throws \Zend\Http\Client\Exception if HTTP request fails or times out
	 */
	public function statusShow($id, array $params = array())
	{
		$this->_init();
		$path = '/1/statuses/show/' . $this->_validInteger($id) . '.xml';
		$_params = array();
		foreach ($params as $key => $value) {
			switch (strtolower($key)) {
				case 'trim_user':
				case 'include_entities':
					$_params[strtolower($key)] = $value ? '1' : '0';
					break;
				default:
					break;
			}
		}
		$response = $this->_get($path, $_params);
		return new \Zend\Rest\Client\Result($response->getBody());
	}

	/**
	 * Retweet a specified status.
	 *
	 * @param string $id
	 * @return \Zend\Rest\Client\Result
	 * @throws \Zend\Http\Client\Exception if HTTP request fails or times out
	 */
	public function statusRetweet($id)
	{
		$this->_init();
		$path = '/1/statuses/retweet/'.$this->_validInteger($id).'.xml';
		$response = $this->_post($path);
		return new \Zend\Rest\Client\Result($response->getBody());
	}

	/**
	 * Public Timeline status
	 *
	 * @see http://dev.twitter.com/doc/get/statuses/public_timeline
	 * @param array $params (optional)
	 * @return \Zend\Rest\Client\Result
	 * @throws \Zend\Http\Client\Exception if HTTP request fails or times out
	 */
	public function statusPublicTimeline(array $params = array())
	{
		$this->_init();
		$path = '/1/statuses/public_timeline';
		$_params = array();
		foreach ($params as $key => $value) {
			switch (strtolower($key)) {
				case 'trim_user':
				case 'include_entities':
					$_params[strtolower($key)] = $value ? '1' : '0';
					break;
				default:
					break;
			}
		}
		$path .= '.xml';
		$response = $this->_get($path, $_params);
		return new \Zend\Rest\Client\Result($response->getBody());
	}

	/**
	 * Home Timeline status
	 *
	 * @see http://dev.twitter.com/doc/get/statuses/home_timeline
	 * @param array $params (optional)
	 * @return \Zend\Rest\Client\Result
	 * @throws \Zend\Http\Client\Exception if HTTP request fails or times out
	 */
	public function statusHomeTimeline(array $params = array())
	{
		$this->_init();
		$path = '/1/statuses/home_timeline';
		$_params = array();
		foreach ($params as $key => $value) {
			switch (strtolower($key)) {
				case 'count':
					$count = (int) $value;
					if (0 >= $count) {
						$count = 1;
					} elseif (200 < $count) {
						$count = 200;
					}
					$_params['count'] = (int) $count;
					break;
				case 'page':
					$_params['page'] = (int) $value;
					break;
				case 'max_id':
				case 'since_id':
					$_params[strtolower($key)] = $this->_validInteger($value);
					break;
				case 'trim_user':
				case 'include_entities':
					$_params[strtolower($key)] = $value ? '1' : '0';
					break;
				default:
					break;
			}
		}
		$path .= '.xml';
		$response = $this->_get($path, $_params);
		return new \Zend\Rest\Client\Result($response->getBody());
	}

	/**
	 * Friend Timeline Status
	 *
	 * @see http://dev.twitter.com/doc/get/statuses/friends_timeline
	 * @param array $params (optional)
	 * @return \Zend\Rest\Client\Result
	 * @throws \Zend\Http\Client\Exception if HTTP request fails or times out
	 */
	public function statusFriendsTimeline(array $params = array())
	{
		$this->_init();
		$path = '/1/statuses/friends_timeline';
		$_params = array();
		foreach ($params as $key => $value) {
			switch (strtolower($key)) {
				case 'count':
					$count = (int) $value;
					if (0 >= $count) {
						$count = 1;
					} elseif (200 < $count) {
						$count = 200;
					}
					$_params['count'] = (int) $count;
					break;
				case 'page':
					$_params['page'] = (int) $value;
					break;
				case 'max_id':
				case 'since_id':
					$_params[strtolower($key)] = $this->_validInteger($value);
					break;
				case 'trim_user':
				case 'include_rts':
				case 'include_entities':
					$_params[strtolower($key)] = $value ? '1' : '0';
					break;
				default:
					break;
			}
		}
		$path .= '.xml';
		$response = $this->_get($path, $_params);
		return new \Zend\Rest\Client\Result($response->getBody());
	}

	/**
	 * User Timeline status
	 *
	 * @see http://dev.twitter.com/doc/get/statuses/user_timeline
	 * @param array $params (optional)
	 * @return \Zend\Rest\Client\Result
	 * @throws \Zend\Http\Client\Exception if HTTP request fails or times out
	 */
	public function statusUserTimeline(array $params = array())
	{
		$this->_init();
		$path = '/1/statuses/user_timeline';
		$_params = array();
		foreach ($params as $key => $value) {
			switch (strtolower($key)) {
				case 'id':
					$path .= '/' . $value;
					break;
				case 'page':
					$_params['page'] = (int) $value;
					break;
				case 'count':
					$count = (int) $value;
					if (0 >= $count) {
						$count = 1;
					} elseif (200 < $count) {
						$count = 200;
					}
					$_params['count'] = $count;
					break;
				case 'screen_name':
					$_params['screen_name'] = $this->_validateScreenName($value);
					break;
				case 'max_id':
				case 'since_id':
				case 'user_id':
					$_params[strtolower($key)] = $this->_validInteger($value);
					break;
				case 'trim_user':
				case 'include_rts':
				case 'include_entities':
					$_params[strtolower($key)] = $value ? '1' : '0';
					break;
				default:
					break;
			}
		}
		$path .= '.xml';
		$response = $this->_get($path, $_params);
		return new \Zend\Rest\Client\Result($response->getBody());
	}
}
