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
*/

namespace Cloud\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Orb\Util\Arrays;

use Application\AdminBundle\Controller\EmailGatewaysController as BaseEmailGatewaysController;
use Application\DeskPRO\Entity\EmailGatewayAddress;
use Application\DeskPRO\Entity\EmailGateway;

class EmailGatewaysController extends BaseEmailGatewaysController
{
	############################################################################
	# list
	############################################################################

	public function listAction()
	{
		$all_gateways = $this->em->createQuery("
			SELECT g
			FROM DeskPRO:EmailGateway g
			ORDER BY g.title ASC
		")->getResult();

		return $this->render('@list.html.twig', array(
			'all_gateways' => $all_gateways,
		));
	}

	############################################################################
	# new-cloud-email
	############################################################################

	public function newCloudEmailAction()
	{
		$name = $this->in->getString('name');

		if (!$name) {
			return $this->createJsonResponse(array('error' => true, 'error_code' => 'empty_name'));
		}

		if (!preg_match('#^[a-zA-Z0-9]+[a-zA-Z0-9\-_]*(?<=[a-zA-Z0-9])$#', $name)) {
			return $this->createJsonResponse(array('error' => true, 'error_code' => 'invalid_name'));
		}

		$dupe = $this->db->fetchColumn("SELECT id FROM email_gateway_addresses WHERE match_pattern = ? LIMIT 1", array($name . '@' . DPC_SITE_DOMAIN));
		if ($dupe) {
			return $this->createJsonResponse(array('error' => true, 'error_code' => 'dupe'));
		}

		$address                 = new EmailGatewayAddress();
		$address->match_type     = 'exact';
		$address->match_pattern  = sprintf("%s@%s", $name, DPC_SITE_DOMAIN);

		$gateway                     = new EmailGateway();
		$gateway->title              = $address->match_pattern;
		$gateway->connection_type    = EmailGateway::CONN_READDIR;
		$gateway->connection_options = '%DP_DATA_DIR%/' . str_replace(array('@', '.'), '_', $address->match_pattern);
		$gateway->gateway_type       = EmailGateway::GATEWAY_TICKETS;

		$gateway->addresses->add($address);
		$address->gateway = $gateway;

		$this->em->persist($address);
		$this->em->persist($gateway);

		$this->db->beginTransaction();
		try {
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'id'      => $gateway->id,
			'title'   => $gateway->title
		));
	}

	####################################################################################################################

	public function editAccountAction($id) { throw $this->createNotFoundException(); }
	public function ajaxTestAction() { throw $this->createNotFoundException(); }
}
