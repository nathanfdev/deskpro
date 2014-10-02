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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace deskpro_magento\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\AgentRequestHandlerInterface;

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
		$url  = $context->getAppSetting('url');
		$user = $context->getAppSetting('api_user');
		$key  = $context->getAppSetting('api_key');

		if (!$url || !$user || !$key) {
			return $context->createJsonResponse(array('error' => 'API URL, user or key missing. Please configure the plugin.'));
		}

		if (!class_exists('\SoapClient')) {
			return $context->createJsonResponse(array('error' => 'SOAP support missing from PHP.'));
		}

		$matches = array();

		$email = $context->getIn()->getString('email');
		if ($email) {
			try {
				$error = error_reporting();
				error_reporting($error & ~E_WARNING);
				$client = new \SoapClient($url . '/api?wsdl');
				error_reporting($error);
			} catch (\SoapFault $e) {
				return $context->createJsonResponse(array('error' => 'Invalid Magento URL'));
			}

			try {
				$session = $client->login($user, $key);
			} catch (\SoapFault $e) {
				return $context->createJsonResponse(array('error' => 'Invalid Magento API user or key'));
			}

			$results = $client->call($session, 'customer.list', array(
				array('email' => $email)
			));

			foreach ($results AS $record) {
				$sales = $client->call($session, 'sales_order.list', array(
					array('customer_id' => $record['customer_id'])
				));

				$orders = array();
				foreach ($sales AS $sale) {
					$orders[] = array(
						'id' => $sale['increment_id'],
						'order_id' => $sale['order_id'],
						'created_at' => $sale['created_at'],
						'grand_total' => number_format($sale['grand_total'], 2),
						'currency' => $sale['order_currency_code'],
						'status' => $sale['status'],
						'url' => $url . '/admin/sales_order/view/order_id/' . $sale['order_id'] . '/',
					);
				}

				$matches[] = array(
					'id' => $record['customer_id'],
					'name' => $record['firstname'] . ' ' . $record['lastname'],
					'email' => $record['email'],
					'profile' => $url . '/admin/customer/edit/id/' . $record['customer_id'] . '/',
					'orders' => $orders
				);
			}

			$client->endSession($session);
		}

		return $context->createJsonResponse(array('matched' => count($matches), 'matches' => $matches));
	}
}