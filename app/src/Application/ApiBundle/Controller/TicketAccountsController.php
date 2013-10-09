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

use Application\DeskPRO\Email\EditTransport;
use Application\DeskPRO\Email\Form\Type\EmailTransportType;
use Application\DeskPRO\Email\IncomingAccount\IncomingAccountTester;
use Application\DeskPRO\Email\OutgoingAccount\OutgoingAccountTester;
use Application\DeskPRO\Entity\EmailGateway;
use Application\DeskPRO\Entity\EmailTransport;
use Application\DeskPRO\TicketAccounts\EditTicketAccount;
use Application\DeskPRO\TicketAccounts\Form\Type\TicketAccountType;

class TicketAccountsController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$data = array();
		$email_accounts = $this->container->getSystemService('ticket_accounts')->getAllAccounts();
		$data['ticket_accounts'] = $this->getApiData($email_accounts, true);

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		$account = $this->container->getSystemService('ticket_accounts')->getById($id);

		if (!$account) {
			throw $this->createNotFoundException();
		}

		$data['ticket_account'] = $account->toApiData();

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		if ($id) {
			$account = $this->container->getSystemService('ticket_accounts')->getById($id);

			if (!$account) {
				throw $this->createNotFoundException();
			}
		} else {
			$account = EmailGateway::createTicketAccount();
		}



		return $this->createApiResponse(array('id' => $account->id, 'success' => true));
	}


	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		$account = $this->container->getSystemService('ticket_accounts')->getById($id);

		if (!$account) {
			throw $this->createNotFoundException();
		}

		$old_id = $account->id;
		$this->em->remove($account);
		$this->em->flush();

		return $this->createSuccessResponse(array('old_id' => $old_id));
	}


	####################################################################################################################
	# test-account
	####################################################################################################################

	public function testAccountAction()
	{
		$account = EmailGateway::createTicketAccount();
		$edit_account = new EditTicketAccount($account);

		$form = $this->createForm(
			new TicketAccountType(),
			$edit_account
		);

		$data = $this->in->getAll('post');
		$form->submit($data);

		$tester = new IncomingAccountTester($edit_account->getIncomingAccount());
		$tester->test();


		return $this->createApiResponse(array(
			'is_success'    => $tester->isSuccess(),
			'log'           => $tester->getLog(),
			'message_count' => $tester->getMessageCount()
		));
	}

	####################################################################################################################
	# test-outgoing-account
	####################################################################################################################

	public function testOutgoingAccountAction()
	{
		$transport = new EmailTransport();
		$edit_transport = new EditTransport($transport);

		$form = $this->createForm(
			new EmailTransportType(),
			$edit_transport
		);

		$data = $this->in->getAll('post');
		$form->submit($data);

		$tester = new OutgoingAccountTester($edit_transport->getOutgoingAccount());
		$tester->test();

		return $this->createApiResponse(array(
			'is_success'    => $tester->isSuccess(),
			'log'           => $tester->getLog(),
			'message_count' => $tester->getMessageCount()
		));
	}
}