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

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class TestController extends AbstractController
{
	/**
	 * /agent/test is an actual skeleton page so you can test widgets
	 * etc without loading the full paned interface
	 */
    public function indexAction()
    {
		$vars = array();

		return $this->render('AgentBundle:Test:test.html.twig', $vars);
	}

	/**
	 * This is a test tab that should be loaded into the interface
	 */
	public function tabAction()
	{
		$vars = array(
			'page_count' => $this->in->getUint('page_count')
		);
		return $this->render('AgentBundle:Test:test-tab.html.twig', $vars);
	}
}
