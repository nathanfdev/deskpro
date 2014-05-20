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

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\UserRules\Form\Type\UserRuleType;
use Application\DeskPRO\UserRules\UserRuleEdit;

class UserRulesController extends AbstractController implements ProtectedControllerInterface
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
		 * @var \Application\DeskPRO\UserRules\UserRules $user_rules
		 */

		$user_rules = $this->container->getSystemService('user_rules');

		return $this->createApiResponse(
			array(
				 'user_rules' => $user_rules->getAllAsArray(),
			)
		);
	}

	###################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		/**
		 * @var \Application\DeskPRO\UserRules\UserRules $user_rules
		 */

		$user_rules = $this->container->getSystemService('user_rules');
		$user_rule  = $user_rules->getWithUsergroup($id);

		if (!$user_rule) {

			throw $this->createNotFoundException();
		}

		return $this->createApiResponse(
			array(
				 'user_rule' => $user_rule
			)
		);
	}

	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		/**
		 * @var \Application\DeskPRO\UserRules\UserRules $user_rules
		 */

		$user_rules = $this->container->getSystemService('user_rules');

		if ($id) {

			$user_rule = $user_rules->getById($id);

			if (!$user_rule) {

				throw $this->createNotFoundException();
			}
		} else {

			$user_rule = $user_rules->createNew();
		}

		$postData = $this->in->getAll('post');

		$user_rule_edit = new UserRuleEdit($user_rule);

		$form = $this->createForm(new UserRuleType(), $user_rule_edit, array('cascade_validation' => true));
		$form->submit($this->deleteExtraDataFromRequest($form, $postData, 'user_rule'), true);

		if ($form->isValid()) {

			$user_rule_edit->save($this->em);

		} else {

			throw ValidationException::create($this->getFormValidationErrorsString($form));
		}

		return $this->createApiResponse(
			array(
				 'success' => true,
				 'id'      => $user_rule->id,
			)
		);
	}

	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		/**
		 * @var \Application\DeskPRO\UserRules\UserRules $user_rules
		 */

		$user_rules = $this->container->getSystemService('user_rules');
		$user_rule  = $user_rules->getById($id);

		if (!$user_rule) {

			throw $this->createNotFoundException();
		}

		$old_id = $user_rule->id;

		$this->db->beginTransaction();

		try {

			$this->em->remove($user_rule);
			$this->em->flush();

			$this->db->commit();

		} catch(\Exception $e) {

			$this->db->rollback();
			throw $e;
		}

		return $this->createSuccessResponse(array('old_id' => $old_id));
	}

	####################################################################################################################
	# apply
	####################################################################################################################

	public function applyAction($id, $page_id)
	{
		/**
		 * @var \Application\DeskPRO\UserRules\UserRules $user_rules
		 */

		$user_rules = $this->container->getSystemService('user_rules');
		$user_rule  = $user_rules->getById($id);

		if (!$user_rule) {

			throw $this->createNotFoundException();
		}

		return $this->createApiResponse($user_rules->applyRuleToUsers($user_rule, $page_id));
	}
}