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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\InstallBundle\Upgrade\Build\Helper201405\LayoutGenerator;
use Application\InstallBundle\Upgrade\Build\Helper201405\LayoutUpgrader;

class Build1400056728 extends AbstractBuild
{
	public function run()
	{
		require_once DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/2014/05/Helper/LayoutGenerator.php';
		require_once DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/2014/05/Helper/LayoutUpgrader.php';

		$this->out("Upgrade ticket layouts");

		$em = $this->container->getEm();
		$db = $this->container->getDb();
		$db->exec("DELETE FROM ticket_layouts");

		#------------------------------
		# Get current layouts
		#------------------------------

		$old_layouts = array();

		$data = $this->getUpgradeData('201404', 'ticket_page_display') ?: array();
		foreach ($data as $r) {
			if (!isset($old_layouts[$r['department_id']])) {
				$old_layouts[$r['department_id'] ?: 0] = array();
			}
			$old_layouts[$r['department_id'] ?: 0][$r['zone']] = unserialize($r['data']);
		}

		$dep_ids = array_keys($old_layouts);
		$deps = array();
		if ($dep_ids) {
			$deps = $em->getRepository('DeskPRO:Department')->getByIds($dep_ids);
		}

		#------------------------------
		# Upgrade layouts
		#------------------------------

		if (!isset($old_layouts[0])) {
			$this->out("No default layout exists, generating one");
			$gen = new LayoutGenerator($this->container);
			$layout = $gen->getTicketLayout();
			$layout->department = null;
			$em->persist($layout);
			$em->flush();
		}

		foreach ($old_layouts as $dep_id => $old) {
			if ($dep_id && !isset($deps[$dep_id])) {
				$this->out("Skipping layout for dep $dep_id because department does not exist");
				continue;
			}

			$form_new  = !empty($old['create']) ? $old['create'] : array();
			$form_view = !empty($old['view']) ? $old['view'] : array();
			$form_edit = !empty($old['modify']) ? $old['modify'] : array();

			$this->out("Upgrading layout for dep $dep_id ...");

			$up = new LayoutUpgrader($form_new, $form_view, $form_edit, $this->container->getTicketFieldManager());
			$layout = $up->getTicketLayout();

			if ($dep_id) {
				$layout->department = $deps[$dep_id];
			}

			$em->persist($layout);
			$em->flush();
		}
	}
}