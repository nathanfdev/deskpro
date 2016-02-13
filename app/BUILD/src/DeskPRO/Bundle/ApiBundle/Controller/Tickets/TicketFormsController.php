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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketFormsController.
 *
 * @ApiModes("all")
 * @Route("/ticket_forms")
 */
class TicketFormsController extends AbstractTicketsController
{
    public static $exposeOnly = [];
    public static $type       = 'ticket_with_layouts';

    /**
     * @ApiDoc(
     *      description="Create a new resource",
     *      requirements={
     *          {
     *              "name"="context",
     *              "requirement"="agent|user",
     *              "description"="Ticket layout context",
     *              "dataType"="string"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          403="Denied"
     *      }
     * )
     * @Post("/{context}", requirements={"context"="(agent|user)"})
     *
     * @param string  $context
     * @param Request $request
     *
     * @return View
     */
    public function postContextAction($context, Request $request)
    {
        return $this->handleForm($this->instantiateEntity($request), $request, [
            'ticket_view_context' => $context,
        ]);
    }

    /**
     * @ApiDoc(
     *      description="Update an existing resource",
     *      requirements={
     *          {
     *              "name"="context",
     *              "requirement"="agent|user",
     *              "description"="Ticket layout context",
     *              "dataType"="string"
     *          },
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          403="Denied"
     *      }
     * )
     * @Put("/{context}/{id}", requirements={"id"="\d+", "context"="(agent|user)"})
     *
     * @param string  $context
     * @param Ticket  $ticket
     * @param Request $request
     *
     * @return View
     */
    public function putContextAction($context, Ticket $ticket, Request $request)
    {
        return $this->handleForm($ticket, $request, [
            'ticket_view_context' => $context,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person'      => $this->getUser(),
            'settings'    => $this->get('brand_stack')->getActive()->getSettings(),
            'use_captcha' => false,
            'for_api'     => true,
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
