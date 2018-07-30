<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\Traits\TicketSearchTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketsPagerTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use DeskPRO\Component\FilterQueryLanguage\QueryUtil;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Util\Arrays;
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
     *              "requirement"="ref:[\w\-\.]+|\d+",
     *              "description"="The id|ref of the resource",
     *              "dataType"="integer|string"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Get("/{id}", requirements={"id"="ref:[\w\-\.]+|\d+"})
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
     *          {"name"="date_created", "description"="date created filter", "dataType"="string", "pattern"="[\w+]"},
     *          {"name"="date_resolved", "description"="date resolved filter", "dataType"="string", "pattern"="[\w+]"},
     *          {"name"="date_last_agent_reply", "description"="date last agent reply filter", "dataType"="string", "pattern"="[\w+]"},
     *          {"name"="date_last_user_reply", "description"="date last user reply filter", "dataType"="string", "pattern"="[\w+]"},
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
     * @throws \Exception
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW_LIST, $this->getPermissionGroupContext($request));

        $offset      = $request->query->getInt('offset');
        $currentPage = !$offset ? $request->query->getInt('page', 1) : null;
        $maxPerPage  = $request->query->getInt('count', self::$listPerPage);
        $meta        = [];

        // if the "ids" param is provided, then just use it to select tickets
        $ids = $request->query->get('ids');
        if ($ids) {
            $ids        = !empty($ids) ? explode(',', $ids) : [];
            $total      = count($ids);
            $maxPerPage = $request->query->getInt('count', min(count($ids), self::$listMaxResults));
        } // otherwise search for IDs using term engine and return Pagerfanta instance
        else {
            try {
                $searchQuery  = $this->get('api_tickets_term_builder')->createSearchQueryFromRequest($request);
                $searchParams = $this->get('api_tickets_term_builder')->createSearchParamsFromRequest($request);
            } catch (\InvalidArgumentException $e) {
                throw $this->createBadRequestException($e->getMessage());
            }

            $ticketFilters = $this->container->get('ticketfilters');
            try {
                $context = $ticketFilters->getAgentContext($this->getUser()->getId());
            } catch (\OutOfBoundsException $e) {
                throw $this->createNotFoundException('failed to get agent model');
            }

            $searcher = $ticketFilters->getArchiveSearcher();
            $qb       = $searcher->getIdsQueryBuilder($searchQuery, $context, $searchParams);

            $this->addLimitInCaseOfNoCriteria($qb, $searchParams, $request->query->all());

            $ids = $qb->execute()->fetchAll(\PDO::FETCH_COLUMN);

            $total       = count($ids);
            $meta['fql'] = $searchQuery;
        }

        if ($offset) {
            $ids    = array_slice($ids, $offset);
            $result = $this->getTicketsOffsetList($total, $ids, $offset, $maxPerPage);
        } else {
            $pageIds = Arrays::getPageChunk($ids, $currentPage, $maxPerPage);
            $result  = $this->getTicketsPager($total, $pageIds, $currentPage, $maxPerPage);
        }

        return View::create($this->wrap($result, $meta));
    }

    /**
     * @Rest\Get("/elastic")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function listElasticAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW_LIST, $this->getPermissionGroupContext($request));

        $offset      = $request->query->getInt('offset');
        $currentPage = !$offset ? $request->query->getInt('page', 1) : null;
        $maxPerPage  = $request->query->getInt('count', self::$listPerPage);
        $meta        = [];

        try {
            $searchQuery  = $this->get('api_tickets_term_builder')->createSearchQueryFromRequest($request, false);
            $searchParams = $this->get('api_tickets_term_builder')->createSearchParamsFromRequest($request);
        } catch (\InvalidArgumentException $e) {
            throw $this->createBadRequestException($e->getMessage());
        }

        $ticketFilters = $this->container->get('ticketfilters');
        try {
            $context = $ticketFilters->getAgentContext($this->getUser()->getId());
        } catch (\OutOfBoundsException $e) {
            throw $this->createNotFoundException('failed to get agent model');
        }

        $qb  = $ticketFilters->getElasticMatcher();
        $ids = $qb->getIds($searchQuery, $context, $searchParams);

        $total       = $qb->getCount($searchQuery, $context, $searchParams);
        $meta['fql'] = $searchQuery;

        if ($offset) {
            $ids    = array_slice($ids, $offset);
            $result = $this->getTicketsOffsetList($total, $ids, $offset, $maxPerPage);
        } else {
            $pageIds = Arrays::getPageChunk($ids, $currentPage, $maxPerPage);
            $result  = $this->getTicketsPager($total, $pageIds, $currentPage, $maxPerPage);
        }

        return View::create($this->wrap($result, $meta));
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
     *              "requirement"="ref:[\w\-\.]+|\d+",
     *              "description"="The id|ref of the resource",
     *              "dataType"="integer|string"
     *          }
     *      },
     *      statusCodes={
     *          204="Returned in case of successful resource modify",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Put("/{id}", requirements={"id"="ref:[\w\-\.]+|\d+"})
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
     *              "requirement"="ref:[\w\-\.]+|\d+",
     *              "description"="The id|ref of the resource",
     *              "dataType"="integer|string"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if everything is ok and there is no such resource anymore",
     *          404="Well, looks like either resource already deleted either it doesn't exists at all"
     *      }
     * )
     * @Rest\Delete("/{id}", requirements={"id"="ref:[\w\-\.]+|\d+"})
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

    /**
     * @param string $value
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return array
     */
    private function parseDateField($value)
    {
        try {
            return QueryUtil::parseDateFieldFromQuery($value);
        } catch (\Exception $e) {
            throw $this->createBadRequestException($e->getMessage());
        }
    }
}
