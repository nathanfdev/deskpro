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
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

class UserRulesController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function listAction()
	{
		// Rules
		$rules = $this->em->getRepository('DeskPRO:UserRule')->findAll();

		// Also organizations with domains
		$org_domains = $this->db->fetchAllGrouped("
			SELECT organization_email_domains.organization_id AS id, organization_email_domains.domain, organizations.name
			FROM organization_email_domains
			LEFT JOIN organizations ON (organizations.id = organization_email_domains.organization_id)
			ORDER BY organizations.name ASC
		", array(), 'id');

		$reg_ug = $this->em->getRepository('DeskPRO:Usergroup')->find(Usergroup::REG_ID);

		return $this->render('AdminBundle:UserRules:list.html.twig', array(
			'rules' => $rules,
			'reg_ug' => $reg_ug,
			'org_domains' => $org_domains,
		));
	}


	############################################################################
	# edit
	############################################################################

	public function editAction($rule_id)
	{
		if ($rule_id) {
			$rule = $this->em->getRepository('DeskPRO:UserRule')->find($rule_id);

			if (!$rule) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
			}
		} else {
			$rule = new \Application\DeskPRO\Entity\UserRule();
		}

		if ($this->in->getBool('process')) {
			$this->ensureRequestToken('edit_userrule');

			$rule->setPatternsString($this->in->getString('rule.patterns_string'));

			$ug = $this->em->find('DeskPRO:Usergroup', $this->in->getUint('rule.add_usergroup'));
			if ($ug) {
				$rule->add_usergroup = $ug;
			}

			$this->em->getConnection()->beginTransaction();
			try {
				$this->em->persist($rule);
				$this->em->flush();

				$this->em->getConnection()->commit();
			} catch (\Exception $e) {
				$this->em->getConnection()->rollback();
				throw $e;
			}

			return $this->redirectRoute('admin_userrules');
		}

		$usergroups = $this->em->getRepository('DeskPRO:Usergroup')->getUsergroupNames();

		return $this->render('AdminBundle:UserRules:edit.html.twig', array(
			'rule' => $rule,
			'usergroups' => $usergroups,
		));
	}


	############################################################################
	# delete
	############################################################################

	public function deleteAction($rule_id)
	{
		$rule = $this->em->getRepository('DeskPRO:UserRule')->find($rule_id);

		if (!$rule) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->ensureRequestToken('delete_userrule');

		$this->em->getConnection()->beginTransaction();
		try {
			$this->em->remove($rule);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_userrules');
	}
}
