<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

/**
 * Handles viewing of deleted items.
 */
class RecycleBinController extends AbstractController
{
    public function listAction()
    {
        $tickets     = $this->_getTickets();
        $ticket_html = false;
        if (!empty($tickets['html'])) {
            $ticket_html = $tickets['html'];
        }

        return $this->render('AgentBundle:RecycleBin:list.html.twig', [
            'tickets_html'            => $ticket_html,
            'tickets_no_more_results' => $tickets['no_more_results'],
        ]);
    }

    public function listMoreAction($type, $page)
    {
        $method = '_get'.ucfirst($type);
        $res    = $this->$method($page);

        $return_res = [
            'html'            => $res['html'],
            'count'           => $res['count'],
            'no_more_results' => $res['no_more_results'],
        ];

        return $this->createJsonResponse($return_res);
    }

    //###########################################################################
    // fetcher methods for different types
    //###########################################################################

    protected function _getTickets($page = 1)
    {
        $per_page = 10;
        $pageinfo = [
            'limit'  => $per_page,
            'offset' => ($page - 1) * $per_page,
        ];

        $searcher = new \Application\DeskPRO\Searcher\TicketSearch();
        $searcher->addTerm('deleted', 'is', 1);

        $results = $searcher->getMatches($pageinfo);

        if (!$results) {
            return ['no_more_results' => true];
        }

        $no_more = false;
        if (count($results < $per_page)) {
            $no_more = true;
        }

        $deleted_tickets = [];
        $tickets         = $this->em->getRepository('DeskPRO:Ticket')->getTicketsFromIds($results);

        $vars = [
            'tickets'         => $tickets,
            'count'           => count($tickets),
            'deleted_tickets' => $deleted_tickets,
            'page'            => $page,
            'no_more_results' => $no_more,
        ];

        $vars['html'] = $this->renderView('AgentBundle:RecycleBin:list-tickets.html.twig', $vars);

        return $vars;
    }
}
