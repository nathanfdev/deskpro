<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\Traits\TicketSearchTrait;
use DeskPRO\Bundle\ApiBundle\EventListener\JsonHeadersResponseListener;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketsPagerTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalTermEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class TicketsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tickets")
 * @ApiDoc(target="all", section="Tickets", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Ticket",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class TicketsController extends AbstractTicketsController
{
    use TicketsPagerTrait, TicketSaveTrait, TicketSearchTrait;

    public static $type = TicketType::class;

    /**
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(
            array_merge($params, $masterRequest->query->all()),
            null,
            ['_controller' => 'ApiBundle:Tickets\Tickets:list']
        );
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @ApiDoc(
     *      description="Get a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="[A-Z0-9-]+",
     *              "description"="The id|ref of the resource",
     *              "dataType"="integer|string"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Get("/{id}", requirements={"id"="[A-Z0-9-]+"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        return parent::getAction($request, $id);
    }

    /**
     * @ApiDoc(
     *      description="Get a list of tickets (see parameters description for additional information)",
     *      filters={
     *          {
     *              "name"="order_by",
     *              "description"="tickets list sort",
     *              "pattern"="id|urgency|date_created|date_last_agent_reply|date_last_user_reply|date_last_reply|date_user_waiting|total_user_waiting|subject|status",
     *              "dataType"="string",
     *          },
     *          {"name"="ids", "description"="ticket list to fetch, comma separated list", "dataType"="string", "pattern"="[\d+,]+"},
     *          {"name"="order_dir", "description"="tickets list sort order", "dataType"="string", "pattern"="asc|desc"},
     *          {"name"="page", "description"="pagination page parameter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="count", "description"="pagination results per page parameter.", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="filter", "description"="TicketFilter ID option", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="labels", "description"="labels filter option", "dataType"="array", "pattern"="[\w+,]+"},
     *          {"name"="star", "description"="star filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="status", "description"="status filter", "dataType"="integer", "pattern"="[\w+]"},
     *          {"name"="not_status", "description"="not status filter", "dataType"="integer", "pattern"="[\w+]"},
     *          {"name"="agent", "description"="agent filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="person", "description"="person filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="language", "description"="language filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="organization", "description"="organization filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="problem", "description"="problem filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="department", "description"="department filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="sla", "description"="sla id filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="sla_status", "description"="sla status filter", "dataType"="integer", "pattern"="ok|warning|fail"},
     *          {
     *              "name"="ticket_field.{id}",
     *              "description"="
     *                  Custom ticket field filter. To filter by a custom field with ID=1 you need to add
     *                  ?ticket_field.1=value to the query string",
     *              "dataType"="string",
     *              "pattern"="\d+|\w+"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if everything is OK",
     *          400="You request was malformed"
     *      }
     * )
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW_LIST, $this->getPermissionGroupContext($request));

        // if the "ids" param is provided, then just use it to select tickets
        $ids = $request->query->get('ids');
        if ($ids) {
            $offset      = $request->query->getInt('offset');
            $currentPage = !$offset ? $request->query->getInt('page', 1) : null;
            $maxPerPage  = $request->query->getInt('count', min(count($ids), self::$listMaxResults));
            $ids         = !empty($ids) ? explode(',', $ids) : [];
            $total       = count($ids);
        } // otherwise search for IDs using term engine and return Pagerfanta instance
        else {
            // todo refactor
            $params = $request->query->all();
            $reset  = [
                'include',
                'count',
                'page',
                'offset',
                'ids_only',
                'inline_sideloads',
                JsonHeadersResponseListener::INCLUDE_HEADERS_PARAM,
            ];

            foreach ($reset as $param) {
                if (array_key_exists($param, $params)) {
                    unset($params[$param]);
                }
            }

            // sort and order params
            if (array_key_exists('order_by', $params)) {
                $allowed = [
                    'id',
                    'urgency',
                    'date_created',
                    'date_last_agent_reply',
                    'date_last_user_reply',
                    'date_last_reply',
                    'date_user_waiting',
                    'total_user_waiting',
                    'subject',
                    'status',
                    'not_status',
                    'sla',
                    'sla_status',
                ];
                $orderBy = $params['order_by'];
                if (!in_array($orderBy, $allowed)) {
                    throw $this->createBadRequestException("Unknown order by option value: $orderBy");
                }

                if ($orderBy === 'date_last_reply') {
                    $orderBy = 'IF(date_last_agent_reply > date_last_user_reply, date_last_agent_reply, date_last_user_reply)';
                }

                unset($params['order_by']);
            } else {
                $orderBy = 'id';
            }
            if (array_key_exists('order_dir', $params)) {
                $orderDir = $params['order_dir'];
                unset($params['order_dir']);
            } else {
                $orderDir = 'asc';
            }

            // Ticket status filter
            if (!array_key_exists('status', $params)) {
                $params['status'] = ['awaiting_user', 'awaiting_agent', 'resolved', 'archived'];
            }

            $term = $this->get('dp.app.term_engine.tickets_select_criteria')->createTerm($params);

            /** @var DbalTermEngine $engine */
            $engine      = $this->get('term_engine.dbal.engine');
            $context     = new TermEngineContext($this->getUser());
            $offset      = $request->query->getInt('offset');
            $currentPage = !$offset ? $request->query->getInt('page', 1) : null;
            $maxPerPage  = $request->query->getInt('count', self::$listPerPage);

            /** @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery $ticketsQuery */
            $ticketsQuery = $engine->evaluate($term, $context);
            $total        = $ticketsQuery->fetchCount();
            $ticketsQuery->setCount($maxPerPage);
            $ticketsQuery->setPage($currentPage);
            $ticketsQuery->setOffset($offset);
            $ticketsQuery->addOrderBy($orderBy, $orderDir);

            $ids = $ticketsQuery->fetchIds();
        }

        if ($offset) {
            $result = $this->getTicketsOffsetList($total, $ids, $offset, $maxPerPage);
        } else {
            $result = $this->getTicketsPager($total, $ids, $currentPage, $maxPerPage);
        }

        return View::create($this->wrap($result));
    }

    /**
     * Get data for export to CSV.
     *
     * @Rest\Get("/csv")
     * @SerializerView(mapping={
     *     "Application\DeskPRO\Entity\Ticket": "DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketCsv"
     * })
     *
     * @param Request $request
     *
     * @return View
     */
    public function csvAction(Request $request)
    {
        return $this->listAction($request);
    }

    /**
     * @ApiDoc(
     *      description="Update an existing resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="[A-Z0-9-]+",
     *              "description"="The id|ref of the resource",
     *              "dataType"="integer|string"
     *          }
     *      },
     *      statusCodes={
     *          204="Returned in case of successful resource modify",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Put("/{id}", requirements={"id"="[A-Z0-9-]+"})
     *
     * @param int     $id
     * @param Request $request
     * @SerializerView(serializeNull=true)
     *
     * @return View
     */
    public function putAction($id, Request $request)
    {
        return parent::putAction($id, $request);
    }

    /**
     * @ApiDoc(
     *      description="Delete a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="[A-Z0-9-]+",
     *              "description"="The id|ref of the resource",
     *              "dataType"="integer|string"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if everything is ok and there is no such resource anymore",
     *          404="Well, looks like either resource already deleted either it doesn't exists at all"
     *      }
     * )
     * @Rest\Delete("/{id}", requirements={"id"="[A-Z0-9-]+"})
     *
     * @param int|string $id
     * @param Request    $request
     *
     * @return View
     */
    public function deleteAction($id, Request $request)
    {
        return parent::deleteAction($id, $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'agent_interface' => true,
            'person'          => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param Ticket $entity
     */
    protected function deleteEntity($entity)
    {
        $entity->disableAutoTicketProcess();
        $entity->setHiddenStatus(Ticket::HIDDEN_STATUS_DELETED);

        $this->saveTicket($entity);
        $entity->deleteTicket($this->getUser(), '', false);
    }
}
