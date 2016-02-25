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
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
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
     * @Get("/new/ticket_filter_sets")
     */
    public function cgetAction()
    {
        $sets = $this->getRepository('App:TicketFilterSet')->findBy([], ['display_order' => 'ASC']);

        return View::create($this->dataSerialize($sets));
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
     * @Get("/new/ticket_filter_sets/{set}")
     *
     * @param TicketFilterSet $set
     *
     * @return View
     */
    public function getAction(TicketFilterSet $set)
    {
        return View::create($this->dataSerialize($set));
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
     * @Post("/ticket_filter_sets")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        return $this->handleFormSubmission($request, new TicketFilterSet());
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
     * @Put("/ticket_filter_sets/{set}")
     *
     * @param Request         $request
     * @param TicketFilterSet $set
     *
     * @return View
     */
    public function putAction(Request $request, TicketFilterSet $set)
    {
        return $this->handleFormSubmission($request, $set);
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
     * @Delete("/ticket_filter_sets/{set}")
     *
     * @param TicketFilterSet $set
     *
     * @return View
     */
    public function deleteAction(TicketFilterSet $set)
    {
        $this->getManager()->remove($set);
        $this->getManager()->flush();

        return View::create([]);
    }

    /**
     * @ApiDoc(
     *      description="Reorder filter sets.",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Post("/ticket_filter_sets/display_order")
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
        foreach ($data['display_order'] as $order => $filter_set_id) {
            $filter_set = $this->getRepository('App:TicketFilterSet')->find($filter_set_id);

            if (!$filter_set) {
                continue;
            }

            $filter_set->setDisplayOrder($order);
            $this->getManager()->persist($filter_set);
            $results[$order] = $filter_set_id;
        }

        $this->getManager()->flush();

        return View::create($this->dataSerialize($results));
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
     * @Get("/new/ticket_filter_sets/{set}/filters")
     *
     * @param TicketFilterSet $set
     *
     * @return View
     */
    public function getSetFiltersAction(TicketFilterSet $set)
    {
        return View::create($this->dataSerialize($set->getFilters()));
    }

    /**
     * @param Request         $request
     * @param TicketFilterSet $set
     *
     * @throws InvalidFormException
     *
     * @return View
     */
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
            $this->getManager()->persist($set);
            $this->getManager()->flush($set);

            return View::create($this->dataSerialize($set), $status);
        } else {
            foreach ($form->getErrors() as $error) {
                echo $error->getMessage()."\n";
            }

            throw new InvalidFormException($form); // let our listeners generate the form error response
        }
    }
}
