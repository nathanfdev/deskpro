<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\Entity;
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
		$org_domains = App::getDb()->fetchAllGrouped("
			SELECT organization_email_domains.organization_id AS id, organization_email_domains.domain, organizations.name
			FROM organization_email_domains
			LEFT JOIN organizations ON (organizations.id = organization_email_domains.organization_id)
			ORDER BY organizations.name ASC
		", array(), 'id');

		return $this->render('AdminBundle:UserRules:list.html.twig', array(
			'rules' => $rules,
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
