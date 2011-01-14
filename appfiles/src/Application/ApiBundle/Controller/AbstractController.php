<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\ApiBundle\Controller;

/**
 * Base API controller.
 */
abstract class AbstractController extends \Application\DeskPRO\Controller\AbstractController
{
	/**
	 * The API key making this request
	 * 
	 * @var Application\DeskPRO\Entity\ApiKey
	 */
	protected $apikey;

	/**
	 * The user context (user making the request, or the one the API key says to use)
	 * @var Application\DeskPRO\Entity\Person
	 */
	protected $user;

	
	protected function init()
	{
		$this->apikey = $this->get('deskpro.api.request_key');

		if ($this->apikey) {
			$this->user = $this->apikey['person'];
		}
	}



	/**
	 * Always require a valid API key.
	 */
	public function preAction($action, $arguments = null)
	{
		if ($this->apikey === null) {
			$response = $this->createResponse('', 401, array(
				'WWW-Authenticate' => 'Basic realm="API"'
			));
			return $response;
		}
	}



	/**
	 * Create an API error response
	 *
	 * @param string $error_code The short error code
	 * @param string $error_message The error message
	 * @param int $status The HTTP status to return
	 * @return Response
	 */
	public function createApiErrorResponse($error_code, $error_message, $status = 400)
	{
		return $this->createJsonResponse(array(
			'error_code' => $error_code,
			'error_message' => $error_message
		), $status);
	}



	/**
	 * Create an API response.
	 *
	 * @param array $data
	 * @param int $status
	 * @return Response
	 */
	public function createApiResponse(array $data, $status = 200)
	{
		return $this->createJsonResponse($data, $status = 200);
	}
}