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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\NewFilters;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Form\Error\ApiErrors;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Exception\TermTypeDoesNotExistException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TicketFilterViewsController.
 *
 * @ApiModes("all")
 */
class TicketFilterViewsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get a list of public filters views",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_filter_views", name="api_ticket_filter_views")
     */
    public function cgetAction()
    {
        $service = $this->get('data.ticket_filter_views');
        $views   = $service->getUnassignedFilterViews();

        return View::create($this->dataSerialize($views), Response::HTTP_OK);
    }

    /**
     * @Get("/ticket_filter_views/{id}", name="get_ticket_filter_views")
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
     * @Get("/ticket_filter_views/{filter}")
     *
     * @param TicketFilter $filter
     *
     * @return View
     */
    public function getAction(TicketFilter $filter)
    {
        return View::create($this->dataSerialize($filter), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="Get ticket filter view counts",
     *      input={"class"="filter","name"=""},
     *      statusCodes={
     *          200="Success"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     *
     * @Get("/ticket_filter_views/{filter}/count")
     *
     * @param Request      $request
     * @param TicketFilter $filter
     *
     * @return View
     */
    public function getTicketsCountAction(Request $request, TicketFilter $filter)
    {
        // Let's retrieve the tickets for this filter.
        $engine = $this->get('term_engine.dbal_ticket_filter_views.engine');
        $conn   = $this->get('database_connection');

        $context = new TermEngineContext($this->getUser());
        // Applying the group-by clauses.
        $group_by = $request->query->get('group_by');
        if ($group_by) {
            $context->addGroupByFromString($group_by);
        }

        $tickets_query = $engine->evaluate($filter, $context);

        if ($group_by) {
            $view_factory = $this->get('api_view_representation_factory');

            return View::create(
                $view_factory->dataSerialize($tickets_query->fetchGroupedCount(), $view_factory::DATATYPE_GROUPED_COUNT),
                Response::HTTP_OK
            );
        } else {
            return View::create(
                $this->dataSerialize([
                    'count' => $tickets_query->fetchCount(),
                ]),
                Response::HTTP_OK
            );
        }
    }

    /**
     * @ApiDoc(
     *      description="Create a filter",
     *      input={"class"="filter","name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilter"
     * )
     *
     * @Post("/ticket_filter_views")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        return $this->handleFormSubmission($request, new TicketFilter());
    }

    /**
     * @ApiDoc(
     *      description="Reorder filters.",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Post("/ticket_filter_views/display_order")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postReorderAction(Request $request)
    {
        $data = $request->request->all();

        if (!is_array($data) || !isset($data['display_order'])) {
            throw new NotFoundHttpException();
        }

        $results = [];
        foreach ($data['display_order'] as $order => $filter_id) {
            $filter = $this->getManager()->find('App:TicketFilter', $filter_id);

            if (!$filter) {
                continue;
            }

            $filter->setDisplayOrder($order);
            $this->getManager()->persist($filter);
            $results[$order] = $filter_id;
        }

        $this->getManager()->flush();

        return View::create($this->dataSerialize($results), Response::HTTP_OK);
    }

    /**
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
     * @Put("/ticket_filter_views/{id}")
     *
     * @param Request      $request
     * @param TicketFilter $filter
     *
     * @return View
     */
    public function putAction(Request $request, TicketFilter $filter)
    {
        return $this->handleFormSubmission($request, $filter);
    }

    /**
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
     * @Delete("/ticket_filter_views/{id}")
     *
     * @param TicketFilter $filter
     *
     * @return View
     */
    public function deleteAction(TicketFilter $filter)
    {
        $this->getManager()->remove($filter);
        $this->getManager()->flush();

        return View::create([]);
    }

    /**
     * we will be making this more abstract for general use by other controllers.
     *
     * @param Request      $request
     * @param TicketFilter $filter
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, TicketFilter $filter)
    {
        $status = $filter->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;
        $form   = $this->get('form.factory')->createNamedBuilder(null, 'filter', $filter)->getForm();

        $submitted = $request->request->all();

        try {
            $form->submit($submitted, $request->getMethod() !== 'PUT');
        } catch (TermTypeDoesNotExistException $e) {
            throw new WrappedApiErrorException(
                new BadRequestHttpException(ApiErrors::TERM_TYPE_DOES_NOT_EXIST),
                [
                    'type' => $e->getMessage(),
                ]
            );
        }

        if ($form->isValid()) {
            $this->getManager()->persist($filter);
            $this->getManager()->flush($filter);

            return View::create(
                $this->dataSerialize($filter),
                $status,
                [
                    'Location' => $this->generateUrl('api_ticket_filter_views_get', ['id' => $filter->getId()]),
                ]
            );
        }

        throw new InvalidFormException($form); // let our listeners generate the form error response
    }
}
