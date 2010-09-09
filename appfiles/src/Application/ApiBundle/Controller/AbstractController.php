<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\ApiBundle\Controller;

/**
 * Base API controller.
 */
abstract class AbstractController extends \DeskPRO\Controller\AbstractController
{
	/**
	 * The API key making this request
	 * 
	 * @var Application\CoreBundle\Entity\ApiKey
	 */
	protected $apikey;


	
	protected function init()
	{
		$this->apikey = $this['deskpro.api.requestapikey'];
	}



	/**
	 * Create an API response
	 *
	 * @param array $data The data to send
	 * @param int $status The HTTP status to return
	 * @return Response
	 */
	public function createApiResponse(array $data, $status = 200)
	{
		$response = $this->container->get('response');
		$response->headers->set('Content-Type', 'application/json');
		$response->setStatusCode(200);
		$response->setContent(json_encode($data));

		return $response;
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
		return $this->createApiResponse(array(
			'error_code' => $error_code,
			'error_message' => $error_message
		), $status);
	}


	
	/**
	 * Called when an API key does not have permission to access a certain action
	 * 
	 * @return Reponse
	 */
	public function apiKeyRequriedAction()
	{
		return $this->createApiErrorResponse(
			'invalid_api_key',
			'API key is invalid or does not have permission to use this resource',
			401
		);
	}
}