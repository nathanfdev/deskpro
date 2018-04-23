<?php

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
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSearchParams;
use DeskPRO\Component\FilterQueryLanguage\QueryUtil;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\StringUtils;
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

        $offset      = $request->query->getInt('offset');
        $currentPage = !$offset ? $request->query->getInt('page', 1) : null;
        $maxPerPage  = $request->query->getInt('count', self::$listPerPage);
        $meta        = [];

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
            $searchParams     = new TicketSearchParams();
            $searchQueryParts = [];

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

            if (array_key_exists('order_dir', $params)) {
                $orderDir = strtoupper($params['order_dir']);
                unset($params['order_dir']);

                if ($orderDir !== 'ASC' && $orderDir !== 'DESC') {
                    throw $this->createBadRequestException("Unknown order dir: $orderDir");
                }
            } else {
                $orderDir = 'asc';
            }

            // sort and order params
            if (array_key_exists('order_by', $params)) {
                $orderByOpt = $params['order_by'];
                unset($params['order_by']);

                switch ($orderByOpt) {
                    case 'id':
                        $orderBy = TicketSearchParams::ORDER_ID;
                        break;
                    case 'urgency':
                        $orderBy = TicketSearchParams::ORDER_URGENCY;
                        break;
                    case 'date_created':
                        $orderBy = TicketSearchParams::ORDER_DATE_CREATED;
                        break;
                    case 'date_last_agent_reply':
                        $orderBy = TicketSearchParams::ORDER_DATE_LAST_AGENT_REPLY;
                        break;
                    case 'date_last_user_reply':
                        $orderBy = TicketSearchParams::ORDER_DATE_LAST_USER_REPLY;
                        break;
                    case 'date_last_reply':
                        $orderBy = TicketSearchParams::ORDER_DATE_LAST_REPLY;
                        break;
                    case 'date_user_waiting':
                        $orderBy = TicketSearchParams::ORDER_DATE_USER_WAITING;
                        break;
                    case 'total_user_waiting':
                        $orderBy = TicketSearchParams::ORDER_DATE_USER_WAITING;
                        break;
                    case 'subject':
                        $orderBy = TicketSearchParams::ORDER_ID; // removed, doesnt make sense?
                        break;
                    case 'status':
                        $orderBy = TicketSearchParams::ORDER_ID; // removed, doesnt make sense?
                        break;
                    case 'not_status':
                        $orderBy = TicketSearchParams::ORDER_ID; // removed, doesnt make sense?
                        break;
                    case 'sla':
                        $orderBy = TicketSearchParams::ORDER_SLA_SEVERITY;
                        break;
                    case 'sla_status':
                        $orderBy = TicketSearchParams::ORDER_SLA_SEVERITY;
                        break;
                    default:
                        throw $this->createBadRequestException("Unknown order by option value: $orderByOpt");
                }
            } else {
                $orderBy = TicketSearchParams::ORDER_ID;
            }

            foreach ($params as $field => $value) {
                $op          = '=';
                $valueQuoted = null;
                $searchField = null;

                switch ($field) {
                    case 'label':
                    case 'labels':
                        $op          = 'IN';
                        $searchField = TermFieldIds::TICKET_LABELS;
                        break;
                    case 'star':
                        $searchField = TermFieldIds::TICKET_STARRED;
                        break;
                    case 'status':
                        $searchField = TermFieldIds::TICKET_STATUS;
                        break;
                    case 'not_status':
                        $op          = '!=';
                        $searchField = TermFieldIds::TICKET_STATUS;
                        break;
                    case 'agent':
                        $searchField = TermFieldIds::TICKET_AGENT;
                        break;
                    case 'agent_team':
                        $searchField = TermFieldIds::TICKET_AGENT_TEAM;
                        break;
                    case 'person':
                    case 'email':
                        $searchField = TermFieldIds::PERSON_ID;
                        break;
                    case 'language':
                        $searchField = TermFieldIds::TICKET_LANGUAGE;
                        break;
                    case 'organization':
                        $searchField = TermFieldIds::ORG_ID;
                        break;
                    case 'problem':
                        $searchField = TermFieldIds::TICKET_PROBLEM_ID;
                        break;
                    case 'department':
                        $searchField = TermFieldIds::TICKET_DEPARTMENT;
                        break;
                    case 'sla':
                        $op          = 'HAS';
                        $searchField = TermFieldIds::TICKET_SLAS;
                        break;
                    case 'urgency':
                        $op          = '=';
                        $searchField = TermFieldIds::TICKET_URGENCY;
                        break;
                    case 'sla_status':
                        if (!is_array($value)) {
                            $value = (array) $value;
                        }

                        $myParts = [];
                        foreach ($value as $slaStatus) {
                            $op = 'HAS';
                            switch ($slaStatus) {
                                case 'ok':
                                    $fn = 'passingSlas()';
                                    break;
                                case 'fail':
                                case 'failing':
                                case 'failed':
                                    $fn = 'failedSlas()';
                                    break;
                                case 'warn':
                                case 'warning':
                                case 'warned':
                                    $fn = 'warningSlas()';
                                    break;
                                default:
                                    throw $this->createBadRequestException("Unknown sla_status value: $value");
                            }
                            $myParts[] = TermFieldIds::TICKET_SLAS." {$op} $fn";
                        }

                        if (!empty($myParts)) {
                            $searchQueryParts[] = '('.implode(' OR ', $myParts).')';
                        }

                        break;
                    default:

                        // renamed field ticket_field.123 -> ticket.data.123
                        if (StringUtils::startsWith('ticket_field_', $field)) {
                            $customFieldId = StringUtils::removeFromStart('ticket_field_', $field);
                            $searchField   = "ticket.data.{$customFieldId}";
                        } else {
                            throw $this->createBadRequestException("Unknown filter termvalue: $field");
                        }
                }

                if ($searchField !== null) {
                    if ($valueQuoted === null) {
                        if ($op === 'IN' && !is_array($value)) {
                            $value = [$value];
                        }
                        if ($op === '=' && is_array($value)) {
                            $op = 'IN';
                        }
                        if ($op === '!=' && is_array($value)) {
                            $op = 'NOT IN';
                        }
                        if (is_array($value)) {

                            // date ranges -> foo BETWEEN DATE('something') AND DATE('else')
                            if (isset($value['from']) || isset($value['to'])) {
                                if (isset($value['from']) && isset($value['to'])) {
                                    $op          = 'BETWEEN';
                                    $valueQuoted = 'DATE('.QueryUtil::quoteValue($value['from']).')'
                                        .' AND '
                                        .'DATE('.QueryUtil::quoteValue($value['to']).')';
                                } elseif (isset($value['from'])) {
                                    $op          = '>=';
                                    $valueQuoted = 'DATE('.QueryUtil::quoteValue($value['from']).')';
                                } else {
                                    $op          = '<=';
                                    $valueQuoted = 'DATE('.QueryUtil::quoteValue($value['to']).')';
                                }
                            } else {
                                if (empty($value)) {
                                    $value = [0];
                                }
                                $valueQuoted = ListUtils::map($value, function ($v) {
                                    return QueryUtil::quoteValue($v);
                                });
                                $valueQuoted = '('.implode(',', $valueQuoted).')';
                            }
                        } else {
                            $valueQuoted = QueryUtil::quoteValue($value);
                        }
                    }

                    $searchQueryParts[] = "{$searchField} {$op} {$valueQuoted}";
                }
            }

            $searchParams->orderBy($orderBy, $orderDir);

            if (!empty($searchQueryParts)) {
                $searchQuery = implode('AND ', $searchQueryParts);
            } else {
                $searchQuery = '';
            }

            $ticketFilters = $this->container->get('ticketfilters');
            try {
                $context = $ticketFilters->getAgentContext($this->getUser()->getId());
            } catch (\OutOfBoundsException $e) {
                throw $this->createNotFoundException('failed to get agent model');
            }

            $parser   = $this->container->get('ticketfilters.queryparser');
            $searcher = $ticketFilters->getSearcher();
            $qb       = $searcher
                ->getIdsQueryBuilder($parser->parseQuery($searchQuery), $context, $searchParams)
                ->setMaxResults(1000);

            $ids         = $qb->execute()->fetchAll(\PDO::FETCH_COLUMN);
            $total       = count($ids);
            $meta['fql'] = $searchQuery;
        }

        if ($offset) {
            $result = $this->getTicketsOffsetList($total, $ids, $offset, $maxPerPage);
        } else {
            $result = $this->getTicketsPager($total, $ids, $currentPage, $maxPerPage);
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
}
