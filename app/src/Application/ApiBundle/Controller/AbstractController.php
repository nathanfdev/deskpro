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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
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
	 * @var \Application\DeskPRO\Entity\ApiKey
	 */
	public $apikey;

	/**
	 * The user context (user making the request, or the one the API key says to use)
	 * @var \Application\DeskPRO\Entity\Person
	 */
	public $person;

	/**
	 * Entity manager
	 * @var \Doctrine\ORM\EntityManager
	 */
	public $em;

	/**
	 * Plain database connection for raw queries
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	public $db;

	/**
	 * Input reader
	 * @var \Orb\Input\Reader\Reader
	 */
	public $in;

	/**
	 * A generic value cleaner
	 * @var \Orb\Input\Cleaner\Cleaner
	 */
	public $cleaner;

	/**
	 * Shared template vars
	 * @var \ArrayObject
	 */
	public $tplvars;

	/**
	 * @var \Application\DeskPRO\Templating\Engine
	 */
	public $tpl;

	/**
	 * Fetch settings
	 * @var \Application\DeskPRO\Settings\Settings
	 */
	public $settings;


	
	protected function init()
	{
		$this->em       = $this->get('doctrine.orm.entity_manager');
		$this->db       = $this->get('database_connection');
		$this->in       = $this->get('deskpro.core.input_reader');
		$this->cleaner  = $this->get('deskpro.core.input_cleaner');
		$this->settings = $this->get('deskpro.core.settings');
		
		$this->apikey = $this->get('deskpro.api.request_key');

		if ($this->apikey) {
			$this->person = $this->apikey['person'];

			$this->person->loadHelper('Agent');
			$this->person->loadHelper('AgentTeam');
			$this->person->loadHelper('AgentPermissions');
			$this->person->loadHelper('PermissionsManager');
			$this->person->loadHelper('HelpMessages');
			$this->person->loadHelper('AgentPrefs');
		}
	}



	/**
	 * Always require a valid API key.
	 */
	public function preAction($action, $arguments = null)
	{
		if (!$this->apikey) {
			$response = $this->createApiErrorResponse('invalid_auth', 'Please provide a valid API key', 401);
			$response->headers->add(array(
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

	public function createApiMultipleErrorResponse(array $errors, $status = 400)
	{
		return $this->createJsonResponse(array(
			'error_code' => 'multiple',
			'errors' => $errors
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



	/**
	 * Creates an API success response
	 *
	 * @return Response
	 */
	public function createSuccessResponse()
	{
		return $this->createApiResponse(array('success' => true));
	}



	public function createApiCreateResponse(array $data, $url)
	{
		$response = $this->createJsonResponse($data, $status = 200);
		$response->headers->add(array('Location' => $url));

		return $response;
	}



	public function getApiData($input, $deep = true)
	{
		if (is_array($input) || $input instanceof \Traversable) {
			$output = array();
			foreach ($input AS $key => $value) {
				if ($value instanceof \Application\DeskPRO\Domain\DomainObject) {
					$output[$key] = $value->toApiData($deep);
				}
			}

			return $output;
		}

		return false;
	}
}
