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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use Application\DeskPRO\Entity\Ticket;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;

class TicketsController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a list of tickets",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/tickets", name="api_tickets")
     * @param Request $request
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $query = $request->query->all();

        $ticketIds = !empty($query['ids']) ? explode(',', $query['ids']) : [];
        $tickets = $this->selectTickets($ticketIds);

        $tickets = $tickets->getResult();

        return View::create(
            $this->dataSerialize($tickets),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a ticket",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the ticket",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskTicket"
     * )
     * @Get("/tickets/{id}", name="api_tickets_get")
     * @param int $id
     * @return View
     */
    public function getAction($id)
    {
        $ticket = $this->getTicket($id);

        if (empty($ticket)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($ticket),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="create a new ticket",
     *      input={"class"="ticket", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskTicket"
     * )
     * @Post("/tickets", name="api_tickets_post")
     * @param Request $request
     * @throws WrappedApiErrorException
     * @throws InvalidFormException
     * @return View
     */
    public function postAction(Request $request)
    {
        $ticket = new Ticket();
        return $this->handleFormSubmission($request, $ticket);
    }

    /**
     * @APIDoc(
     *      description="update a ticket",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the ticket",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="ticket", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/tickets/{id}", name="api_tickets_put")
     * @param Request $request
     * @param $id
     * @throws WrappedApiErrorException
     * @return View
     */
    public function putAction(Request $request, $id)
    {
        $ticket = $this->getTicket($id);

        return $this->handleFormSubmission($request, $ticket);
    }

    /**
     * @APIDoc(
     *      description="delete a ticket",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the ticket",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Delete("/tickets/{id}", name="api_tickets_delete")
     * @param $id
     * @return View
     */
    public function deleteAction($id)
    {
        $ticket = $this->getTicket($id);

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        $this->getDoctrine()->getManager()->remove($ticket);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

    /**
     * Retrieve a single ticket
     * @param int $id
     * @return Ticket
     */
    protected function getTicket($id)
    {
        $id = (int) $id;
        $ticket = $this->getDoctrine()->getManager()->getRepository('DeskPRO:Ticket')->find($id);

        if (!$ticket) {
            throw $this->createNotFoundException();
        }

        return $ticket;
    }

    /**
     * Will be abstracted for use by other controllers
     * @param Request $request
     * @param Ticket $ticket
     * @return View
     * @throws WrappedApiErrorException
     */
    protected function handleFormSubmission(Request $request, Ticket $ticket)
    {
        $status = $ticket->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        $submitted = $request->request->all();

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(
            null,
            'ticket',
            $ticket,
            ['ticket' => $ticket, 'entity_manager' => $this->getDoctrine()->getManager()]
        )->getForm();

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($ticket);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_tickets_get', array('id' => $ticket->getId()));

            return View::create(
                $this->dataSerialize($ticket),
                $status,
                array(
                    'Location' => $location,
                )
            );
        }

        throw new InvalidFormException($form);
    }

    /**
     * Get a Doctrine Query for getting certain tickets
     * @param $ticketIds
     * @return \Doctrine\ORM\Query
     */
    protected function selectTickets($ticketIds = [])
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Clean the IDs
        $ticketIds = array_map(function($value) {
            return (int) $value;
        }, $ticketIds);

        $query = $entityManager->createQueryBuilder()->select('t')->from('DeskPRO:Ticket', 't');

        if (!empty($ticketIds)) {
            $query = $query->where('t.id IN (:ticketIds)')
                ->setParameter('ticketIds', $ticketIds);
        }

        $query = $query->orderBy('t.subject', 'ASC');

        return $query->getQuery();
    }
}
