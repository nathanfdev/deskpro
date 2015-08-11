<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Filters;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\ApiErrors;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\TermEngine\Exception\TermTypeDoesNotExistException;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;

use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;

use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;

class FiltersController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get a list of filters",
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
    public function cgetAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = $this->get('data.filters')->getFiltersPager($page, $count);

        if (!$pager) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->DataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @Get("/ticket_filters/{id}", name="get_ticket_filters")
     *
     * @ApiDoc(
     *      description="get a filter",
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
            $this->DataSerialize($filter),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a filter's count",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          },
     *          {
     *              "name"="group_by",
     *              "requirement"=".+",
     *              "description"="the grouping order you want",
     *              "dataType"="string",
     *              "required"=false
     *          },
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     * @Get("/ticket_filters/{id}/count")
     */
    public function getTicketsCountAction(Request $request, $id)
    {
        $filters = $this->get('data.filters');
        $filter = $filters->getFilter($id);

        if (!$filter) {
            throw $this->createNotFoundException();
        }

        // Let's retrieve the tickets for this filter.
        $engine = $this->get('term_engine.dbal_ticket_filters.engine');
        $conn = $this->get('database_connection');

        $context = new TermEngineContext($this->getUser());
        // Applying the group-by clauses.
        $groupby = $request->query->get('group_by');
        if ($groupby) {
            $context->addGroupByFromString($groupby);
        }

        $tickets_query = $engine->evaluate($filter, $context);

        if ($groupby) {
            $view_factory = $this->get('api_view_representation_factory');
            return View::create(
                $this->DataSerialize(new PrimitiveArray($tickets_query->fetchGroupedCount(), $view_factory::DATATYPE_GROUPED_COUNT)),
                Response::HTTP_OK
            );
        } else {
            return View::create(
                $this->DataSerialize(new PrimitiveArray(array(
                    'count' => $tickets_query->fetchCount()
                ))),
                Response::HTTP_OK
            );
        }
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
     *      input={"Array"},
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
            $this->DataSerialize($results),
            Response::HTTP_OK
        );
    }

    /**
     * @Put("/ticket_filters/{id}", name="put_ticket_filters")
     *
     * @ApiDoc(
     *      description="modify a filter",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="filter","name"=""},
     *      statusCodes={
     *          204="Updated",
     *          404="Not Found",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     *
     * @Put("/ticket_filters/{id}", name="api_ticket_filters_put")
     */
    public function putAction(Request $request, $id)
    {
        $filter = $this->get('data.filters')->getFilter($id);

        if (!$filter) {
            throw new NotFoundHttpException();
        }

        return $this->handleFormSubmission($request, $filter);
    }

    /**
     * @ApiDoc(
     *      description="get a list of filters",
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
        $filter = $filters->getFilter($id);

        if (!$filter) {
            throw $this->createNotFoundException();
        }

        // Let's retrieve the tickets for this filter.
        $engine = $this->get('term_engine.dbal_ticket_filters.engine');
        $context = new TermEngineContext($this->getUser());
        $tickets_query = $engine->evaluate($filter, $context);

        $tickets_query->setCount($request->query->get('count', 10));
        $tickets_query->setPage($request->query->get('page', 1));

        return View::create(
            $this->DataSerialize($tickets_query->fetchAll()),
            Response::HTTP_OK
        );
    }

    /**
     * @Delete("/ticket_filters/{id}")
     *
     * @ApiDoc(
     *      description="delete a filter",
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

    /**
     * we will be making this more abstract for general use by other controllers
     */
    protected function handleFormSubmission(Request $request, TicketFilter $filter)
    {
        $status = $filter->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        $form = $this->get('form.factory')->createNamedBuilder(null, 'filter', $filter)->getForm();

        $submitted = $request->request->all();

        try {
            $form->submit($submitted, $request->getMethod() !== 'PUT');
        } catch (TermTypeDoesNotExistException $e) {
            throw new WrappedApiErrorException(
                new BadRequestHttpException(ApiErrors::TERM_TYPE_DOES_NOT_EXIST),
                array(
                    'type' => $e->getMessage()
                )
            );
        }

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($filter);
            $this->getDoctrine()->getManager()->flush($filter);

            return View::create(
                $this->DataSerialize($filter),
                $status,
                array(
                    'Location' => $this->generateUrl('api_ticket_filters_get', array('id' => $filter->getId()))
                )
            );
        }

        throw new InvalidFormException($form); // let our listeners generate the form error response
    }

    // A bit of comfort.
    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }
}
