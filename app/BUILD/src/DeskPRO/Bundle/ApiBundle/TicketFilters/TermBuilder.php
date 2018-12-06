<?php

namespace DeskPRO\Bundle\ApiBundle\TicketFilters;

use DeskPRO\Bundle\ApiBundle\EventListener\JsonHeadersResponseListener;
use DeskPRO\Bundle\AppBundle\TicketFilters\TermFieldIds;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSearchParams;
use DeskPRO\Component\FilterQueryLanguage\Parser;
use DeskPRO\Component\FilterQueryLanguage\QueryUtil;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\StringUtils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TermBuilder.
 */
class TermBuilder
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * Constructor.
     *
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param Request $request
     * @param bool    $excludeHidden
     *
     * @throws \InvalidArgumentException
     *
     * @return \DeskPRO\Component\FilterQueryLanguage\Query\Query
     */
    public function createSearchQueryFromRequest(Request $request, $excludeHidden = true)
    {
        $searchQueryParts = [];

        $params = $request->query->all();
        $reset  = [
            'order_by',
            'order_dir',
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

        // default to searching non-hidden
        if ($excludeHidden && !isset($params['status']) && !isset($params['not_status'])) {
            // please note that we use this status to check if we need a limit below
            $params['not_status'] = 'hidden';
        }

        foreach ($params as $field => $value) {
            $op          = '=';
            $valueQuoted = null;
            $searchField = null;

            switch ($field) {
                case 'id':
                    $op          = 'IN';
                    $searchField = TermFieldIds::TICKET_ID;
                    break;
                case 'label':
                case 'labels':
                    $op          = 'IN';
                    $searchField = TermFieldIds::TICKET_LABELS;
                    break;
                case 'star':
                    $searchField = TermFieldIds::TICKET_STARRED;
                    break;
                case 'status':
                    $query = $this->getStatusFilterQuery($value, false);
                    if ($query) {
                        $searchQueryParts[] = $query;
                    }
                    break;
                case 'not_status':
                    $query = $this->getStatusFilterQuery($value, true);
                    if ($query) {
                        $searchQueryParts[] = $query;
                    }
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
                                throw new \InvalidArgumentException("Unknown sla_status value: $value");
                        }
                        $myParts[] = TermFieldIds::TICKET_SLAS." {$op} $fn";
                    }

                    if (!empty($myParts)) {
                        $searchQueryParts[] = '('.implode(' OR ', $myParts).')';
                    }

                    break;
                case 'date_created':
                    list($op, $valueQuoted) = $this->parseDateField($value);
                    $searchQueryParts[]     = TermFieldIds::TICKET_DATE_CREATED." {$op} {$valueQuoted}";
                    break;
                case 'date_resolved':
                    list($op, $valueQuoted) = $this->parseDateField($value);
                    $searchQueryParts[]     = TermFieldIds::TICKET_DATE_RESOLVED." {$op} {$valueQuoted}";
                    break;
                case 'date_last_agent_reply':
                    list($op, $valueQuoted) = $this->parseDateField($value);
                    $searchQueryParts[]     = TermFieldIds::TICKET_DATE_LAST_AGENT_REPLY." {$op} {$valueQuoted}";
                    break;
                case 'date_last_user_reply':
                    list($op, $valueQuoted) = $this->parseDateField($value);
                    $searchQueryParts[]     = TermFieldIds::TICKET_DATE_LAST_USER_REPLY." {$op} {$valueQuoted}";
                    break;
                default:

                    // renamed field ticket_field.123 -> ticket.data.123
                    if (StringUtils::startsWith('ticket_field_', $field)) {
                        $customFieldId = StringUtils::removeFromStart('ticket_field_', $field);
                        $searchField   = "ticket.data.{$customFieldId}";
                    } else {
                        throw new \InvalidArgumentException("Unknown filter termvalue: $field");
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
                        if (!empty($value['from']) || !empty($value['to'])) {
                            if (!empty($value['from']) && !empty($value['to'])) {
                                $op          = 'BETWEEN';
                                $valueQuoted = 'DATE('.QueryUtil::quoteValue($value['from']).')'
                                    .' AND '
                                    .'DATE('.QueryUtil::quoteValue($value['to']).')';
                            } elseif (!empty($value['from'])) {
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

        if (!empty($searchQueryParts)) {
            $searchQuery = implode(' AND ', $searchQueryParts);
        } else {
            $searchQuery = '';
        }

        return $this->parser->parseQuery($searchQuery);
    }

    /**
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     *
     * @return TicketSearchParams
     */
    public function createSearchParamsFromRequest(Request $request)
    {
        $params       = $request->query->all();
        $searchParams = new TicketSearchParams();

        if (array_key_exists('order_dir', $params)) {
            $orderDir = strtoupper($params['order_dir']);
            if ($orderDir !== 'ASC' && $orderDir !== 'DESC') {
                throw new \InvalidArgumentException("Unknown order dir: $orderDir");
            }
        } else {
            $orderDir = 'asc';
        }

        // sort and order params
        if (array_key_exists('order_by', $params)) {
            $orderByOpt = $params['order_by'];

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
                    throw new \InvalidArgumentException("Unknown order by option value: $orderByOpt");
            }
        } else {
            $orderBy = TicketSearchParams::ORDER_ID;
        }

        $searchParams->orderBy($orderBy, $orderDir);

        return $searchParams;
    }

    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function parseDateField($value)
    {
        try {
            return QueryUtil::parseDateFieldFromQuery($value);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @param mixed $value
     * @param bool  $isNot
     *
     * @return string|null
     */
    private function getStatusFilterQuery($value, $isNot = false)
    {
        if (!is_array($value)) {
            $value = (array) $value;
        }

        $myParts = [];
        foreach ($value as $status) {
            if (!$value) {
                continue;
            }

            if (strpos($status, '.') !== false) {
                list($status, $statusId) = explode('.', $status, 2);
                $part                    = sprintf(
                    "(%s = '%s' AND %s = %s)",
                    TermFieldIds::TICKET_STATUS, $status,
                    TermFieldIds::TICKET_TICKET_STATUS_ID, $statusId
                );
                if ($isNot) {
                    $part = '(NOT'.$part.')';
                }
                $myParts[] = $part;
            } else {
                $op        = $isNot ? '!=' : '=';
                $myParts[] = sprintf("(%s %s '%s')", TermFieldIds::TICKET_STATUS, $op, $status);
            }
        }

        if (!$myParts) {
            return null;
        }

        if (count($myParts) > 1) {
            $query = '('.implode(' OR ', $myParts).')';
        } else {
            $query = $myParts[0];
        }

        return $query;
    }
}
