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
use Application\DeskPRO\Entity;

use Application\AdminBundle\Form\EditTicketPriorityType;

/**
 * Misc
 */
class TicketFeaturesController extends AbstractController
{
	############################################################################
	# index
	############################################################################

	public function indexAction()
	{
 		return $this->render('AdminBundle:TicketFeatures:index.html.twig', array(

		));
	}

	############################################################################
	# refill-search-tables
	############################################################################

	public function regenSearchAction()
	{
		$res = '<pre>';
		$res .= 'Regenerating tickets_search_active table ... ';

		$time_start = microtime(true);
		App::getEntityRepository('DeskPRO:Ticket')->fillSearchTable();
		$time_end = microtime(true);

		$res .= sprintf("Done in %.4f seconds\n", $time_end-$time_start);
		$res .= '</pre>';

		return new \Symfony\Component\HttpFoundation\Response($res);
	}
}
