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

namespace deskpro_highrise;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\AgentRequestHandlerInterface;
use Orb\Service\Highrise\Highrise;
use Orb\Service\Highrise\Resource\Person as HighrisePerson;

class AgentRequestHandler implements AgentRequestHandlerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function handleAgentRequest(AgentRequestContext $context)
	{
		if ($context->getAction() == 'find-email') {
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
		$url   = $context->getAppSetting('api_url');
		$token = $context->getAppSetting('api_token');

		if (!$url || !$token) {
			return $context->createJsonResponse(array('error' => 'API token or URL missing. Please configure the plugin.'));
		}

		$parts = parse_url($url);
		$url = $parts['scheme'] . '://' . $parts['host'];

		$matches = array();

		$email = $context->getIn()->getString('email');
		if ($email) {
			$highrise = new Highrise($url, $token);
			$personApi = new HighrisePerson($highrise);
			try {
				$error = error_reporting();
				error_reporting($error & ~E_WARNING);

				$output = $personApi->findPeopleWithCriteria(array('email' => $email));

				error_reporting($error);
			} catch (\Exception $e) {
				return $context->createJsonResponse(array('error' => 'Invalid Highrise API URL or token.', 'error_type' => get_class($e), 'error_code' => $e->getCode(), 'error_message' => $e->getMessage()));
			}

			foreach ($output AS $person) {
				if (isset($person['first-name'], $person['last-name'])) {
					$name = $person['first-name'] . ' ' . $person['last-name'];
				} else if (isset($person['first-name'])) {
					$name = $person['first-name'];
				} else if (isset($person['last-name'])) {
					$name = $person['last-name'];
				} else {
					$name = 'Unknown';
				}

				if (isset($person['contact-data']['email-addresses'][0]['address'])) {
					$email = $person['contact-data']['email-addresses'][0]['address'];
				} else {
					$email = 'Unknown';
				}

				if (isset($person['title'], $person['company-name'])) {
					$companyTitle = $person['title'] . ' @ ' . $person['company-name'];
				} else if (isset($person['title'])) {
					$companyTitle = $person['title'];
				} else if (isset($person['company-name'])) {
					$companyTitle = $person['company-name'];
				} else {
					$companyTitle = false;
				}

				$matches[] = array(
					'id' => $person['id'],
					'name' => $name,
					'email' => $email,
					'title' => isset($person['title']) ? $person['title'] : '',
					'company' => isset($person['company-name']) ? $person['company-name'] : '',
					'profile' => $url . '/people/' . $person['id']
				);
			}
		}

		return $context->createJsonResponse(array('matched' => count($matches), 'matches' => $matches));
	}
}