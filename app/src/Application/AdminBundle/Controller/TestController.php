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

use Application\DeskPRO\App;

/**
 * Handles creating/editing of Usersources
 */
class TestController extends AbstractController
{
	public function indexAction()
	{
		$filter = new \Orb\Assetic\Filter\CssGradientImage();
		$im = $filter->getGradientImage(50, '#FFD237', '#604D0E', 'vertical', 1, 200);

		header("Content-type: image/png");
		imagepng($im);

		exit;
		$AGENTGROUP_ALL = $this->em->find('DeskPRO:Usergroup', 6);

		$scanner = new \Application\InstallBundle\Data\AgentGroupPermScanner();
		foreach ($scanner->getNames() as $p_name) {
			$p = new \Application\DeskPRO\Entity\Permission();
			$p->usergroup = $AGENTGROUP_ALL;
			$p->name = $p_name;
			$p->value = 1;
			$this->em->persist($p);
		}
		$this->em->flush();

		return $this->createResponse('');
	}
}
