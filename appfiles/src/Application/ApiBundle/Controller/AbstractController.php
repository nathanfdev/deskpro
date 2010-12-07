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
abstract class AbstractController extends \Application\DeskPRO\Controller\AbstractController
{
	/**
	 * The API key making this request
	 * 
	 * @var Application\DeskPRO\Entity\ApiKey
	 */
	protected $apikey;

	/**
	 * Flag set in other API controllers that requires a person
	 * to perform any actions.
	 */
	protected $require_person = true;


	
	protected function init()
	{
		$this->apikey = $this['deskpro.api.requestapikey'];
	}



	/**
	 * Always require a valid API key.
	 */
	public function preAction($action, $arguments = null)
	{
		if ($this->apikey === null) {
			return $this->createApiErrorResponse(
				'invalid_api_key',
				'Invalid API key',
				'401'
			);
		}

		if ($this->require_person AND !$this->getPersonContext()) {
			return $this->createApiErrorResponse(
				'invalid_person',
				'Invalid Person',
				'401'
			);
		}
	}


	
	/**
	 * If an action is done on the behalf of a person, then we'll need to have
	 * those credentials passed.
	 *
	 * @return Person
	 */
	public function getPersonContext()
	{
		// TODO: Work out how to auth other sites or apps etc
		// oauth?

		$person = null;
		if (isset($this->session) AND $this->session->get('auth_person_id')) {
			try {
				$person = $this->em->find('DeskPRO:Person', $this->session->get('auth_person_id'));
			} catch (Exception $e) {}
		}

		return $person;
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
}