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
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API access to TicketFilterSet entities.
 *
 * @ApiModes("all")
 */
class TicketFilterSetsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get the list of ticket filter sets available",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_filter_sets", name="api_ticket_filter_sets")
     */
    public function cgetAction(Request $request)
    {
        $sets = $this->getEm()
            ->getRepository('App:TicketFilterSet')
            ->findBy(array(), array('display_order' => 'ASC'));

        return View::create(
            $this->dataSerialize($sets),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Get a filter set",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter set",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet"
     * )
     *
     * @Get("/ticket_filter_sets/{id}", name="api_ticket_filter_sets_get")
     */
    public function getAction($id)
    {
        $set = $this->getEm()->find('App:TicketFilterSet', $id);

        if (!$set) {
            throw new NotFoundHttpException();
        } else {
            return View::create(
                $this->dataSerialize($set),
                Response::HTTP_OK
            );
        }
    }

    /**
     * @ApiDoc(
     *      description="Add a new filter set.",
     *      input={"class"="ticket_filter_set", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request",
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet"
     * )
     *
     * @Post("/ticket_filter_sets", name="api_ticket_filter_sets_post")
     */
    public function postAction(Request $request)
    {
        $set = new TicketFilterSet();

        return $this->handleFormSubmission($request, $set);
    }

    /**
     * @ApiDoc(
     *      description="Edit an existing filter set",
     *      input={"class"="ticket_filter_set", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request",
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet"
     * )
     *
     * @Put("/ticket_filter_sets/{id}", name="api_ticket_filter_sets_put")
     */
    public function putAction(Request $request, $id)
    {
        $set = $this->getEm()->find('App:TicketFilterSet', $id);

        if (!$set) {
            throw new NotFoundHttpException();
        } else {
            return $this->handleFormSubmission($request, $set);
        }
    }

    /**
     * @ApiDoc(
     *      description="delete a filter set",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter set",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Deleted",
     *          404="Not Found"
     *      }
     * )
     *
     * @Delete("/ticket_filter_sets/{id}", name="api_ticket_filter_sets_delete")
     */
    public function deleteAction($id)
    {
        $this->getDoctrine()->getManager()->remove($this->findOr404('App:TicketFilterSet', $id));
        $this->getDoctrine()->getManager()->flush();

        return View::create([], Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="Reorder filter sets.",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Post("/ticket_filter_sets/display_order", name="api_ticket_filter_sets_display_order_post")
     */
    public function postReorderAction(Request $request)
    {
        $data = $request->request->all();

        if (!is_array($data) || !isset($data['display_order'])) {
            throw new NotFoundHttpException();
        }

        $results = array();
        foreach ($data['display_order'] as $order => $filter_set_id) {
            $filter_set = $this->getEm()->find('App:TicketFilterSet', $filter_set_id);

            if (!$filter_set) {
                continue;
            }

            $filter_set->setDisplayOrder($order);
            $this->getEm()->persist($filter_set);
            $results[$order] = $filter_set_id;
        }

        $this->getEm()->flush();

        return View::create(
            $this->dataSerialize($results),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Get the filters within a filter set",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the filter set",
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
     * @Get("/ticket_filter_sets/{id}/filters", name="api_ticket_filter_set_filters")
     */
    public function getTicketsFiltersSetFiltersAction(Request $request, $id)
    {
        $set = $this->getEm()->find('App:TicketFilterSet', $id);

        if (!$set) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($set->getFilters()),
            Response::HTTP_OK
        );
    }

    protected function handleFormSubmission(Request $request, TicketFilterSet $set)
    {
        $status = $set->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        $form = $this->get('form.factory')
            ->createNamedBuilder(null, 'filter_set', $set)
            ->getForm();

        $submitted = $request->request->all();

        if (array_key_exists('is_default', $submitted)) {
            $submitted['is_default'] = $submitted['is_default'] == true;
        }

        $form->submit($submitted, 'PUT' !== $request->getMethod());

        if ($form->isValid()) {
            $this->getEm()->persist($set);
            $this->getEm()->flush($set);

            return View::create(
                $this->dataSerialize($set),
                $status,
                array(
                    'Location' => $this->generateUrl('api_ticket_filter_sets_get', array('id' => $set->getId())),
                )
            );
        } else {
            foreach ($form->getErrors() as $error) {
                echo $error->getMessage()."\n";
            }
            throw new InvalidFormException($form); // let our listeners generate the form error response
        }
    }

    // A bit of comfort.
    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }
}
