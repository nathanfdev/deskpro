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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\Filters;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Entity\PersonSetting;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\TicketsSettings;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TicketFiltersController.
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
     * @Get("/ticket_filters", name="api_ticket_filters")
     */
    public function getAllAction()
    {
        return View::create(
            $this->dataSerialize($this->get('data.filters')->getFilters()),
            Response::HTTP_OK
        );
    }

    /**
     * @Get("/ticket_filters/{id}", name="get_ticket_filters")
     *
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
     * @Get("/ticket_filters/{id}", name="api_ticket_filters_get")
     */
    public function getAction($id)
    {
        $filter = $this->get('data.filters')->getFilter($id);

        if (!$filter) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($filter),
            Response::HTTP_OK
        );
    }

    /**
     * @Post("/ticket_filters", name="post_ticket_filters")
     *
     * @ApiDoc(
     *      description="create a filter",
     *      input={"class"="filter","name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     *
     * @Post("/ticket_filters", name="api_ticket_filters_post")
     */
    public function postAction(Request $request)
    {
        $filter = new TicketFilter();

        return $this->handleFormSubmission($request, $filter);
    }

    /**
     * @ApiDoc(
     *      description="Reorder filters.",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Post("/ticket_filters/display_order", name="api_ticket_filters_display_order_post")
     */
    public function postReorderAction(Request $request)
    {
        $data = $request->request->all();

        if (!is_array($data) || !isset($data['display_order'])) {
            throw new NotFoundHttpException();
        }

        $results = array();
        foreach ($data['display_order'] as $order => $filter_id) {
            $filter = $this->getEm()->find('App:TicketFilter', $filter_id);

            if (!$filter) {
                continue;
            }

            $filter->setDisplayOrder($order);
            $this->getEm()->persist($filter);
            $results[$order] = $filter_id;
        }

        $this->getEm()->flush();

        return View::create(
            $this->dataSerialize($results),
            Response::HTTP_OK
        );
    }

    /**
     * @Put("/ticket_filters/{id}", name="put_ticket_filters")
     *
     * @ApiDoc(
     *      description="Modify filter grouping",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          },
     *          {
     *              "name"="group_by",
     *              "description"="new filter grouping",
     *              "dataType"="string"
     *          }
     *      },
     *      statusCodes={
     *          204="Updated",
     *          404="Not Found",
     *          400="Bad Request"
     *      }
     * )
     *
     * @Put("/ticket_filters/{id}", name="api_ticket_filters_put")
     */
    public function putAction(Request $request, $id)
    {
        $filter  = $this->findOr404(TicketFilter::class, $id);
        $content = json_decode($request->getContent(), true);

        // Remove existing setting if got no or empty group_by
        if (!array_key_exists('group_by', $content) || !$content['group_by']) {
            $this->removeFilterGroupByPersonSetting($filter);

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        // Save the group_by in filter setting
        else {
            $setting = $this->findOrCreateFilterGroupByPersonSetting($filter);
            $setting->setValue($content['group_by']);
            $this->getManager()->persist($setting);
            $this->getManager()->flush();

            return new Response(null, Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * @param TicketFilter $filter
     *
     * @return PersonSetting
     */
    private function findOrCreateFilterGroupByPersonSetting(TicketFilter $filter)
    {
        $settingName = TicketsSettings::FILTER_GROUPING_PREFIX.$filter->getId();
        $person      = $this->getUser();

        $personSetting = $this->getManager()->find(PersonSetting::class, ['person' => $person, 'name' => $settingName]);
        if (!$personSetting) {
            $personSetting = new PersonSetting($person, $settingName);
        }

        return $personSetting;
    }

    /**
     * @param TicketFilter $filter
     */
    private function removeFilterGroupByPersonSetting(TicketFilter $filter)
    {
        $settingName = TicketsSettings::FILTER_GROUPING_PREFIX.$filter->getId();
        $person      = $this->getUser();

        $personSetting = $this->getManager()->find(PersonSetting::class, ['person' => $person, 'name' => $settingName]);
        if ($personSetting) {
            $this->getManager()->remove($personSetting);
            $this->getManager()->flush();
        }
    }

    /**
     * @ApiDoc(
     *      description="Get filter's tickets",
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
     * @Get("/ticket_filters/{id}/tickets", name="api_ticket_filter_tickets_get")
     */
    public function getTicketFilterTickets(Request $request, $id)
    {
        $filters = $this->get('data.filters');
        $filter  = $filters->getFilter($id);

        if (!$filter) {
            throw $this->createNotFoundException();
        }

        // Let's retrieve the tickets for this filter.

        /** @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngine $engine */
        $engine        = $this->get('term_engine.dbal_ticket_filters.engine');
        $context       = new TermEngineContext($this->getUser());
        $tickets_query = $engine->evaluate($filter, $context);

        $currentPage = $request->query->get('page', 1);
        $maxPerPage  = $request->query->get('count', 10);

        $tickets_query->setCount($maxPerPage);
        $tickets_query->setPage($currentPage);
        $ticket_ids = $tickets_query->fetchIds();
        $tickets    = $this->getEm()->getRepository('DeskPRO:Ticket')->findBy(['id' => $ticket_ids]);

        // retrieve total count and wrap results in Pagerfanta

        $total        = $this->get('data.tickets.ticket_counts')->getTicketFilterCount($filter)->getCount();
        $pagerAdapter = new FixedAdapter($total, $tickets);
        $pager        = new Pagerfanta($pagerAdapter);
        $pager->setCurrentPage($currentPage);
        $pager->setMaxPerPage($maxPerPage);

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @Delete("/ticket_filters/{id}")
     *
     * @ApiDoc(
     *      description="Delete a filter",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Deleted",
     *          404="Not Found"
     *      }
     * )
     *
     * @Delete("/ticket_filters/{id}", name="api_ticket_filters_delete")
     */
    public function deleteAction($id)
    {
        $filter = $this->get('data.filters')->getFilter($id);

        if (!$filter) {
            throw $this->createNotFoundException();
        }

        $this->getDoctrine()->getManager()->remove($filter);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

    // A bit of comfort.
    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }
}
