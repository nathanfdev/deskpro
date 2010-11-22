<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Controller;

/**
 * Handles ticket searches
 */
class TicketSearchController extends AbstractController
{
	############################################################################
	# /tech/ticket-search
	############################################################################


	############################################################################
	# /tech/ticket-search/filters-pane
	############################################################################

	public function filtersPane()
	{
		return $this->render('TechBundle:TicketSearch:pane-filters');
	}
}