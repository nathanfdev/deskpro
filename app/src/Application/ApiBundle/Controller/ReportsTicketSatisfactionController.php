<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

class ReportsTicketSatisfactionController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction($page)
	{
		/**
		 * @var \Application\DeskPRO\Reports\TicketSatisfaction $reports_ticket_satisfaction
		 */

		$reports_ticket_satisfaction = $this->container->getSystemService('reports_ticket_satisfaction');
		$html_vars                   = $reports_ticket_satisfaction->getVarsForFeedHtmlView($page);

		return $this->createApiResponse(
			array(
				 'page'        => $html_vars['page'],
				 'num_pages'   => $html_vars['num_pages'],
				 'html'        => $this->renderView(
					 'ReportsInterfaceBundle:TicketSatisfaction:results-feed.html.twig',
					 $html_vars
				 ),
			)
		);
	}

	####################################################################################################################
	# summary
	####################################################################################################################

	public function summaryAction($date)
	{
		/**
		 * @var \Application\DeskPRO\Reports\TicketSatisfaction $reports_ticket_satisfaction
		 */

		$reports_ticket_satisfaction = $this->container->getSystemService('reports_ticket_satisfaction');
		$html_vars                   = $reports_ticket_satisfaction->getVarsForSummaryHtmlView($date);


		return $this->createApiResponse(
			array(
				 'html' => $this->renderView(
					 'ReportsInterfaceBundle:TicketSatisfaction:results-summary.html.twig',
					 $html_vars
				 ),
			)
		);
	}
}