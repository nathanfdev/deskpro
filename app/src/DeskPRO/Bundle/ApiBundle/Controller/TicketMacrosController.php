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
namespace DeskPRO\Bundle\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to languages.
 */
class TicketMacrosController extends BaseController
{
    /**
     * Retrieve the list of custom fields available for tickets.
     *
     * @Get("/ticket_macros", name="api_ticket_layouts")
     */
    public function getTicketMacros()
    {
        $service = $this->get('data.ticket_macros');

        return View::create(
            $this->dataSerialize($service->loadAll()),
            Response::HTTP_OK
        );
    }

    /**
     * @Get("/ticket_macros/{id}", name="api_ticket_layouts_single")
     */
    public function getSingleAction($id)
    {
        $service = $this->get('data.ticket_macros');

        return View::create(
            $this->dataSerialize($service->loadSingle($id)),
            Response::HTTP_OK
        );
    }

    /**
     * @Get("/users/{user_id}/ticket_macros")
     */
    public function loadPersonTicketMacros($user_id)
    {
        if ($user_id == 'me') {
            $user    = $this->getUser();
            $user_id = $user->getId();
        }

        $service = $this->get('data.ticket_macros');

        return View::create(
            $this->dataSerialize($service->loadForPerson($user_id))
        );
    }
}
