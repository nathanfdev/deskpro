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

use Aws\CloudWatch\Exception\InvalidFormatException;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\ApiErrors;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;

/**
 * API access to TicketFilterSet entities.
 */
class TicketFilterSetsController extends BaseController
{
    /**
     * @Get("/ticket_filter_sets", name="all_ticket_filter_sets")
     *
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
            ->findAll();

        return View::create(
            $this->createRepresentation($sets),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a filter set",
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
                $this->createRepresentation($set),
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

    protected function handleFormSubmission(Request $request, TicketFilterSet $set)
    {
        $status = $set->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;
        $form = $this->get('form.factory')
            ->createNamedBuilder(null, 'filter_set', $set)
            ->getForm();

        $form->submit($request->request->all(), 'PUT' !== $request->getMethod());

        if ($form->isValid()) {
            $this->getEm()->persist($set);
            $this->getEm()->flush($set);

            return View::create(
                $this->createRepresentation($set),
                $status,
                array(
                    'Location' => $this->generateUrl('api_ticket_filter_sets_get', array('id' => $set->getId()))
                )
            );
        } else {
            throw new InvalidFormException($form); // let our listeners generate the form error response
        }
    }

    // A bit of comfort.
    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }
}
