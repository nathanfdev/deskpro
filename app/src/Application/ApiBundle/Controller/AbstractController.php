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
	public $apikey;

	/**
	 * The user context (user making the request, or the one the API key says to use)
	 * @var Application\DeskPRO\Entity\Person
	 */
	public $user;

	/**
	 * Entity manager
	 * @var Doctrine\ORM\EntityManager
	 */
	public $em;

	/**
	 * Plain database connection for raw queries
	 * @var Application\DeskPRO\DBAL\Connection
	 */
	public $db;

	/**
	 * Input reader
	 * @var Orb\Input\Reader\Reader
	 */
	public $in;

	/**
	 * A generic value cleaner
	 * @var Orb\Input\Cleaner\Cleaner
	 */
	public $cleaner;

	/**
	 * Shared template vars
	 * @var ArrayObject
	 */
	public $tplvars;

	/**
	 * @var Application\DeskPRO\Templating\Engine
	 */
	public $tpl;

	/**
	 * Fetch settings
	 * @var Application\DeskPRO\Settings\Settings
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