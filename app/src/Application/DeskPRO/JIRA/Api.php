<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\JIRA;


use Application\DeskPRO\Service\JIRA;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Api
{
	const API_BASE_PATH = 'rest/api/2';

	protected $service;
	protected $oauth;

	public function __construct(JIRA $service)
	{
		$this->service = $service;
	}

	/**
	 * @return OAuthWrapper
	 */
	public function getOAuth()
	{
		if (!$this->oauth) {
			$this->oauth = new OAuthWrapper($this->service);
		}

		return $this->oauth;
	}

	/**
	 * @param $endpoint
	 * @param string $method
	 * @param array $headers
	 * @param array $params
	 * @throws \Exception
	 */
	public function call($endpoint, $method = 'GET', array $headers = array(), $params = array())
	{
		try {

			return $this->getOAuth()->getClient()
				->{strtolower($method)}(self::API_BASE_PATH . $endpoint, $headers, $params)
				->send()
				->json();

		} catch (ClientErrorResponseException $e) {
			$code = $e->getResponse()->getStatusCode();

			if (404 === $code) {
				throw new NotFoundHttpException($e->getResponse()->getReasonPhrase());
			}

			// todo log error message
			// $json['errorMessages']
			$json = $e->getResponse()->json();

			if (!empty($json['errors'])) {
				throw new ApiErrorsException($json['errors']);
			}

			throw new \Exception($e->getResponse()->getReasonPhrase(), $code);
		}
	}

	/**
	 * @param $endpoint
	 * @param array $params
	 * @return mixed
	 */
	public function get($endpoint, array $params = array())
	{
		return $this->call($endpoint, 'GET', array(), array('query' => $params));
	}

	/**
	 * @param $endpoint
	 * @param array $params
	 * @return mixed
	 */
	public function post($endpoint, array $params = array())
	{
		return $this->call($endpoint, 'POST', array('content-type' => 'application/json'), json_encode($params));
	}

	/**
	 * @param $endpoint
	 * @param array $params
	 * @return mixed
	 */
	public function put($endpoint, array $params = array())
	{
		return $this->call($endpoint, 'PUT', array('content-type' => 'application/json'), json_encode($params));
	}

	/**
	 * @param $endpoint
	 * @param array $params
	 * @return mixed
	 */
	public function delete($endpoint, array $params = array())
	{
		return $this->call($endpoint, 'DELETE', array(), $params);
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	public function createIssue(array $data)
	{
		return $this->post('/issue', $data);
	}

	/**
	 * @param $json
	 * @throws ApiErrorsException
	 * @throws \Exception
	 */
	public function createIssueJson($json)
	{
		return $this->call('/issue', 'POST', array('content-type' => 'application/json'), $json);
	}
} 