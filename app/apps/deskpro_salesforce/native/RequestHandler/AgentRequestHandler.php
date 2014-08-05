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
 * @category Entities
 */

namespace deskpro_salesforce\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\AgentRequestHandlerInterface;
use Application\DeskPRO\Entity\DataStore;
use DeskPRO\Kernel\KernelErrorHandler;

class AgentRequestHandler implements AgentRequestHandlerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function handleAgentRequest(AgentRequestContext $context)
	{
		if ($context->getAction() == 'call-api') {
			return $this->callApiAction($context);
		} else {
			throw $context->createNotFoundException();
		}
	}

	/**
	 * @param AgentRequestContext $context
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	private function callApiAction(AgentRequestContext $context)
	{
		$user     = $context->getAppSetting('api_user');
		$password = $context->getAppSetting('api_password');
		$token    = $context->getAppSetting('api_security_token');

		if (!$user || !$password || !$token) {
			return $context->createJsonResponse(array('error' => 'API user, password or token missing. Please configure the plugin.'));
		}

		$matches = array();

		$email = $context->getIn()->getString('email');
		if ($email) {
			try {
				$error = error_reporting();
				error_reporting($error & ~E_WARNING);

				require_once(DP_ROOT . '/vendor-src/salesforce/SforcePartnerClient.php');
				$sforce = new \SforcePartnerClient();
				$sforce->createConnection(DP_ROOT . '/vendor-src/salesforce/partner.wsdl.xml');

				error_reporting($error);
			} catch (\SoapFault $e) {
				return $context->createJsonResponse(array('error' => 'Invalid Salesforce URL.'));
			}

			try {
				$sforce->login($user, $password . $token);
			} catch (\SoapFault $e) {
				if ($e->getMessage()) {
					return $context->createJsonResponse(array('error' => 'Salesforce error: ' . $e->getMessage()));
				}
				return $context->createJsonResponse(array('error' => 'Invalid Salesforce API user, password, or token.'));
			}

			$fields = $this->getFields($context, $sforce);

			try {
				$matches = $this->lookupUsers($email, $fields, $context, $sforce);
			} catch (\SoapFault $e) {
				// If its an invalid field error, someone could have changed fields within
				// sf so one is now invlaid. so force a refresh of the cache then try again
				if ($e->getMessage() == 'INVALID_FIELD') {
					$fields = $this->getFields($context, $sforce, true);
					$matches = $this->lookupUsers($email, $fields, $context, $sforce);
				} else {
					throw $e;
				}
			}
		}

		return $context->createJsonResponse(array(
			'matches' => $matches
		));
	}


	/**
	 * @param string $email
	 * @param array $fields
	 * @param AgentRequestContext $context
	 * @param \SforcePartnerClient $sforce
	 * @return array
	 */
	private function lookupUsers($email, $fields, AgentRequestContext $context, \SforcePartnerClient $sforce)
	{
		$matches = array();

		$fields_list = implode(', ', $fields);

		try {
			$response = $sforce->query("
				SELECT $fields_list
				FROM Contact
				WHERE Email = '" . addslashes($email) . "'
			");
		} catch (\Exception $e) {
			$response = null;
			KernelErrorHandler::logException($e, false, 'salesforce_' . $e->getMessage());
		}

		if ($response) {
			foreach ($response->records AS $record) {
				if (@$record->fields->Title && @$record->fields->Department) {
					$departmentTitle = @$record->fields->Department . ', ' . @$record->fields->Title;
				} else {
					$departmentTitle = @$record->fields->Department . @$record->fields->Title;
				}

				$matches[] = array(
					'id' => $record->Id,
					'name' => @$record->fields->FirstName . ' ' . @$record->fields->LastName,
					'email' => @$record->fields->Email,
					'title' => @$record->fields->Title,
					'department' => @$record->fields->Department,
					'departmentTitle' => @$departmentTitle,
					'profile' => 'https://na8.salesforce.com/' . $record->Id
				);
			}
		}

		return $matches;
	}


	/**
	 * @param AgentRequestContext $context
	 * @param \SforcePartnerClient $sforce
	 * @param bool $force_reset
	 * @return array
	 */
	private function getFields(AgentRequestContext $context, \SforcePartnerClient $sforce, $force_reset = false)
	{
		$data_id = 'apps.' . $context->getApp()->id . '.fields';

		$data = $context->getEm()->getRepository('DeskPRO:DataStore')->getByName($data_id);
		if ($force_reset || !$data || $data->getData('ts_created') < time()-28800) {
			$data = null;
		}

		if (!$data) {
			$fields = array();

			$desc = $sforce->describeSObject('Contact');

			foreach ($desc->fields as $f) {
				switch ($f->name) {
					case 'Id':
						$fields[] = 'Id';
						break;
					case 'FirstName':
						$fields[] = 'FirstName';
						break;
					case 'LastName':
						$fields[] = 'LastName';
						break;
					case 'Email':
						$fields[] = 'Email';
						break;
					case 'Title':
						$fields[] = 'Title';
						break;
					case 'Department':
						$fields[] = 'Department';
						break;
				}
			}

			$context->getDb()->delete('datastore', array('name' => $data_id));

			$data = new DataStore();
			$data->name = $data_id;
			$data->setData('fields', $fields);
			$data->setData('ts_created', time());
			$context->getEm()->persist($data);
			$context->getEm()->flush($data);
		}

		return $data->getData('fields', array());
	}
}