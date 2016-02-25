<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\LegacyFilters;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Filters;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketFiltersController.
 *
 * @ApiModes("all")
 */
class TicketFiltersController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get a list of filters",
     *      parameters={
     *          {
     *              "name"="page",
     *              "requirement"="\d+",
     *              "description"="the page you are requesting",
     *              "dataType"="integer",
     *              "required"=false
     *          },
     *          {
     *              "name"="count",
     *              "requirement"="\d+",
     *              "description"="results per page",
     *              "dataType"="integer",
     *              "required"=false
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_filters")
     */
    public function cgetAction()
    {
        $filters     = new Filters();
        $all_filters = $filters->getFiltersForPerson($this->getUser());

        return View::create($this->dataSerialize($all_filters));
    }

    /**
     * @ApiDoc(
     *      description="Get a filter",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     *
     * @Get("/ticket_filters/{filter}")
     *
     * @param LegacyTicketFilter $filter
     *
     * @return View
     */
    public function getAction(LegacyTicketFilter $filter)
    {
        return View::create($this->dataSerialize($filter));
    }

    /**
     * @ApiDoc(
     *      description="Get filter's tickets. See /tickets endpoint docs for the parameter details.",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_filters/{id}/tickets")
     *
     * @param Request            $request
     * @param LegacyTicketFilter $filter
     *
     * @return Response
     */
    public function getFilterTicketsAction(Request $request, LegacyTicketFilter $filter)
    {
        /** @var Person $user */
        $user = $this->getUser();
        $user->loadHelper('AgentTeam');
        $user->loadHelper('AgentPermissions');

        $current_page = $request->query->getInt('page', 1);
        $max_per_page = $request->query->getInt('count', 10);

        $searcher = $filter->getSearcher();
        $searcher->setPersonContext($user);

        $ticket_ids = $searcher->getMatches([
            'limit'  => $max_per_page,
            'offset' => $max_per_page * ($current_page - 1),
        ]);

        $tickets = $this->getRepository(Ticket::class)->findBy(['id' => $ticket_ids]);
        $pager   = new Pagerfanta(new FixedAdapter($searcher->getCount(), $tickets));

        $pager->setMaxPerPage($max_per_page);
        $pager->setCurrentPage($current_page);

        return View::create($this->dataSerialize($pager));
    }
}
