<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Orb
 *
 * @package Orb
 * @subpackage Service
 * @category Zendesk
 */

namespace Orb\Service\Zendesk;

/**
 * @see http://developer.zendesk.com/documentation/rest_api/introduction.html
 */
class Zendesk
{
	const POST   = 'POST';
	const GET    = 'GET';
	const PUT    = 'PUT';
	const DELETE = 'DELETE';

	/**
	 * @var string
	 */
	protected $api_key;

	/**
	 * @var string
	 */
	protected $user_id;

	/**
	 * @var string
	 */
	protected $zendesk_url;

	/**
	 * @var int
	 */
	protected $timeout = 10;


	/**
	 * Get your $api_key from Settings > Channels > API.
	 * If you use your password instead of a token, prefix it with password:. Ex: $api_key = "password:secretpassword".
	 *
	 * The user is your user account that all calls will be made from (your email address).
	 *
	 * @param string $zendesk_url   The API endpoint. You may pass just a domain name to use the default v2 API endpoint.
	 * @param string $user_id       The user to use
	 * @param string $api_key       API Key
	 */
	public function __construct($zendesk_url, $user_id, $api_key)
	{
		// "password:" prefix is our own invention so we
		// know its not a token,
		if (preg_match('#^password:#', $api_key)) {
			$api_key = preg_replace('#^password:#', '', $api_key);
		} else {
			// If its a token we need to ensure the token: prefix
			if (!preg_match('#^token:#', $api_key)) {
				$api_key = "token:$api_key";
			}
		}

		$this->api_key = $api_key;
		$this->user_id = $user_id;

		// Not a URL, assume we got just a domain
		if (!preg_match('#^https?://#', $zendesk_url)) {
			$zendesk_url = 'https://' . $zendesk_url . '/api/v2';
		} else {
			$zendesk_url = rtrim($zendesk_url, '/');
		}

		$this->zendesk_url = $zendesk_url;
	}


	/**
	 * @param int $timeout
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = (int)$timeout;
	}


	/**
	 * @param string $id
	 * @return string
	 */
	public function getUrlForEndpoint($id)
	{
		return $this->zendesk_url . '/' . $id . '.json';
	}


	/**
	 * Send a GET request
	 *
	 * @return \Orb\Service\Zendesk\ApiResponse
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Orb\Service\Zendesk\ApiException
	 */
	public function sendGet($id)
	{
		return $this->sendRequest($id, self::GET, null);
	}


	/**
	 * Send a DELETE request
	 *
	 * @return \Orb\Service\Zendesk\ApiResponse
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Orb\Service\Zendesk\ApiException
	 */
	public function sendDelete($id)
	{
		return $this->sendRequest($id, self::DELETE, null);
	}


	/**
	 * Send a PUT request
	 *
	 * @param string $id
	 * @param array $call_data
	 * @return \Orb\Service\Zendesk\ApiResponse
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Orb\Service\Zendesk\ApiException
	 */
	public function sendPut($id, array $call_data)
	{
		return $this->sendRequest($id, self::PUT, $call_data);
	}


	/**
	 * Send a GET request
	 *
	 * @param string $id
	 * @param array $call_data
	 * @return \Orb\Service\Zendesk\ApiResponse
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Orb\Service\Zendesk\ApiException
	 */
	public function sendPost($id, array $call_data)
	{
		return $this->sendRequest($id, self::POST, $call_data);
	}


	/**
	 * Send an API request
	 *
	 * @param string $id
	 * @param string $action
	 * @param array  $call_data   The set
	 * @return \Orb\Service\Zendesk\ApiResponse
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws \Orb\Service\Zendesk\ApiException
	 */
	public function sendRequest($id, $action, array $call_data = null)
	{
		#------------------------------
		# Set up cURL
		#------------------------------

		if ($call_data) {
			$call_json = json_encode($call_data);
			if (!$call_json) {
				throw new \InvalidArgumentException("Could not encode call data", -1);
			}
		} else {
			$call_json = '[]';
		}

		$url = $this->getUrlForEndpoint($id);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, $this->user_id."/".$this->api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		curl_setopt($ch, CURLOPT_USERAGENT, "DeskPRO_Orb/1.0");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

		switch($action){
			case self::POST:
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $call_json);
				break;
			case self::GET:
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
				break;
			case self::PUT:
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $call_json);
				break;
			case self::DELETE:
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
			default:
				throw new \InvalidArgumentException("Invalid request action `$action` must be one of: POST, GET, PUT, DELETE", -1);
				break;
		}

		#------------------------------
		# Make the call
		#------------------------------

		$output = curl_exec($ch);

		if (curl_errno($ch)) {
			throw new \RuntimeException(sprintf("cURL Error: %s: %s", curl_errno($ch), curl_error($ch)));
		}

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($output === false || !$http_code) {
			throw new ApiException("Request failed", ApiException::REQUEST_FAILED, null, $output);
		}

		curl_close($ch);

		$response = new ApiResponse($http_code, $output);

		return $response;
	}
}