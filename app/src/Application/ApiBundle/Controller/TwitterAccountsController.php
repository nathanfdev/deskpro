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
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\TwitterAccounts\Form\Type\TwitterAccountType;
use Application\DeskPRO\TwitterAccounts\TwitterAccountEdit;

class TwitterAccountsController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}


	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		/**
		 * @var \Application\DeskPRO\TwitterAccounts\TwitterAccounts $twitter_accounts
		 */
		$twitter_accounts = $this->container->getSystemService('twitter_accounts');

		return $this->createApiResponse(array(
			'twitter_accounts' => $twitter_accounts->getAllWithUserAsArray()
		));
	}

	###################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		/**
		 * @var \Application\DeskPRO\TwitterAccounts\TwitterAccounts $twitter_accounts
		 */
		$twitter_accounts = $this->container->getSystemService('twitter_accounts');
		$twitter_account  = $twitter_accounts->getWithUserById($id);

		if (!$twitter_account) {
			throw $this->createNotFoundException();
		}

		$returnedData               = $twitter_account;
		$returnedData['all_agents'] = $twitter_accounts->getAllAgents();

		return $this->createApiResponse(array(
			'twitter_account' => $returnedData
		));
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		/**
		 * @var \Application\DeskPRO\TwitterAccounts\TwitterAccounts $twitter_accounts
		 */
		$twitter_accounts = $this->container->getSystemService('twitter_accounts');

		if ($id) {
			$twitter_account = $twitter_accounts->getById($id);
			if (!$twitter_account) {
				throw $this->createNotFoundException();
			}
		} else {
			$twitter_account = $twitter_accounts->createNew();
		}

		$postData = $this->in->getAll('post');

		$twitter_account_edit = new TwitterAccountEdit($twitter_account);

		$form = $this->createForm(new TwitterAccountType(), $twitter_account_edit, array('cascade_validation' => true));
		$form->submit($this->deleteExtraDataFromRequest($form, $postData, 'twitter_account'), true);

		if ($form->isValid()) {
			$twitter_account_edit->save($this->em);
		} else {
			throw ValidationException::create($this->getFormValidationErrorsString($form));
		}

		return $this->createApiResponse(array(
			'success' => true,
			'id'      => $twitter_account->id,
		));
	}

	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		/**
		 * @var \Application\DeskPRO\TwitterAccounts\TwitterAccounts $twitter_accounts
		 */
		$twitter_accounts = $this->container->getSystemService('twitter_accounts');
		$twitter_account  = $twitter_accounts->getById($id);

		if (!$twitter_account) {
			throw $this->createNotFoundException();
		}

		$old_id = $twitter_account->id;

		$this->db->beginTransaction();

		try {
			$this->em->remove($twitter_account);
			$this->em->flush();
			$this->db->commit();
		} catch(\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createSuccessResponse(array('old_id' => $old_id));
	}
}