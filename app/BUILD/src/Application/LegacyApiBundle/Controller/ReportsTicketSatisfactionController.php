<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class ReportsTicketSatisfactionController extends AbstractController
{
    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction($page)
    {
        /*
         * @var \Application\DeskPRO\Reports\TicketSatisfaction
         */
        $reports_ticket_satisfaction = $this->container->getSystemService('reports_ticket_satisfaction');
        $html_vars                   = $reports_ticket_satisfaction->getVarsForFeedHtmlView($page);

        return $this->createApiResponse(
            [
                 'page'      => $html_vars['page'],
                 'num_pages' => $html_vars['num_pages'],
                 'html'      => $this->renderView(
                     'ReportsInterfaceBundle:TicketSatisfaction:results-feed.html.twig',
                     $html_vars
                 ),
            ]
        );
    }

    //###################################################################################################################
    // summary
    //###################################################################################################################

    public function summaryAction($date)
    {
        /*
         * @var \Application\DeskPRO\Reports\TicketSatisfaction
         */
        $reports_ticket_satisfaction = $this->container->getSystemService('reports_ticket_satisfaction');
        $html_vars                   = $reports_ticket_satisfaction->getVarsForSummaryHtmlView($date);

        return $this->createApiResponse(
            [
                 'html' => $this->renderView(
                     'ReportsInterfaceBundle:TicketSatisfaction:results-summary.html.twig',
                     $html_vars
                 ),
            ]
        );
    }
}
