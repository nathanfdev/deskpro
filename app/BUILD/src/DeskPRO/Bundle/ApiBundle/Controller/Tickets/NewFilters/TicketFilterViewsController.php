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
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFilterType;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Exception\TermTypeDoesNotExistException;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class TicketFilterViewsController.
 *
 * @ApiModes("all")
 */
class TicketFilterViewsController extends BaseController
{
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
            return View::create($this->dataSerialize([
                'count' => $tickets_query->fetchCount(),
            ]));
        }
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
        $form   = $this->get('form.factory')->createNamedBuilder(null, TicketFilterType::class, $filter)->getForm();

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

            return View::create($this->dataSerialize($filter), $status);
        }

        throw new InvalidFormException($form); // let our listeners generate the form error response
    }
}
