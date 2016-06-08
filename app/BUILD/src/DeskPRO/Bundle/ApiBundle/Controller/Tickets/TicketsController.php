<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\EventListener\JsonHeadersResponseListener;
use DeskPRO\Bundle\ApiBundle\Traits\Labels\LabelsHelper;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketsPagerTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
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
 */
class TicketsController extends AbstractTicketsController
{
    use LabelsHelper, TicketsPagerTrait, TicketSaveTrait;

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
        $request = $masterRequest->duplicate(array_merge($params, $masterRequest->query->all()), null, [
            '_controller' => 'ApiBundle:Tickets\Tickets:list',
        ]);
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @deprecated
     * @Rest\Post("")
     */
    public function postAction(Request $request)
    {
        $this->get('logger')->warning("POST /tickets is deprecated, use /ticket_forms to create tickets");

        return parent::postAction($request);
    }

    /**
     * @ApiDoc(
     *      description="Get a list of tickets (see parameters description for additional information)",
     *      filters={
     *          {
     *              "name"="sort",
     *              "description"="tickets list sort",
     *              "pattern"="id|urgency|date_created|date_last_agent_reply|date_last_user_reply|date_last_reply|date_user_waiting|total_user_waiting|subject|status",
     *              "dataType"="string",
     *          },
     *          {"name"="ids", "description"="ticket list to fetch, comma separated list", "dataType"="string", "pattern"="[\d+,]+"},
     *          {"name"="order", "description"="tickets list sort order", "dataType"="string", "pattern"="asc|desc"},
     *          {"name"="page", "description"="pagination page parameter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="count", "description"="pagination results per page parameter.", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="filter", "description"="TicketFilter ID option", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="labels", "description"="labels filter option", "dataType"="array", "pattern"="[\w+,]+"},
     *          {"name"="star", "description"="star filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="status", "description"="status filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="agent", "description"="agent filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="person", "description"="person filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="language", "description"="language filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="organization", "description"="organization filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="problem", "description"="problem filter", "dataType"="integer", "pattern"="\d+"},
     *          {"name"="department", "description"="department filter", "dataType"="integer", "pattern"="\d+"},
     *          {
     *              "name"="ticket_field.{id}",
     *              "description"="
     *                  Custom ticket field filter. To filter by a custom field with ID=1 you need to add
     *                  ?ticket_field.1=value to the query string",
     *              "dataType"="string",
     *              "pattern"="\d+|\w"
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
            $currentPage = $request->query->getInt('page', 1);
            $maxPerPage  = $request->query->getInt('count', min(count($ids), self::$listMaxResults));
            $ids         = !empty($ids) ? explode(',', $ids) : [];
            $total       = count($ids);
        }

        // otherwise search for IDs using term engine and return Pagerfanta instance
        else {
            // todo refactor
            $params = $request->query->all();
            $reset  = ['include', 'count', 'page', 'ids_only', JsonHeadersResponseListener::INCLUDE_HEADERS_PARAM];

            foreach ($reset as $param) {
                if (array_key_exists($param, $params)) {
                    unset($params[$param]);
                }
            }

            // sort and order params
            if (array_key_exists('order_by', $params)) {
                $allowed = [
                    'id', 'urgency', 'date_created', 'date_last_agent_reply', 'date_last_user_reply',
                    'date_last_reply', 'date_user_waiting', 'total_user_waiting', 'subject', 'status',
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
            $currentPage = $request->query->getInt('page', 1);
            $maxPerPage  = $request->query->getInt('count', self::$listPerPage);

            /** @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery $ticketsQuery */
            $ticketsQuery = $engine->evaluate($term, $context);
            $total        = $ticketsQuery->fetchCount();
            $ticketsQuery->setCount($maxPerPage);
            $ticketsQuery->setPage($currentPage);
            $ticketsQuery->addOrderBy($orderBy, $orderDir);

            $ids = $ticketsQuery->fetchIds();
        }

        return View::create($this->wrap($this->getTicketsPager($total, $ids, $currentPage, $maxPerPage)));
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'agent_interface' => true,
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
