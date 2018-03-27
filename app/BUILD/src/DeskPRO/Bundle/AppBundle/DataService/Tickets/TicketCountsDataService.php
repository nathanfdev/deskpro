<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService\Tickets;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use DeskPRO\Bundle\AppBundle\Model\TicketGrouping;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class TicketCountsDataService.
 */
class TicketCountsDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DbalTicketFilterEngine
     */
    private $engine;

    /**
     * @var TokenStorageInterface
     */
    private $token_storage;

    /**
     * Constructor.
     *
     * @param EntityManager          $em
     * @param DbalTicketFilterEngine $engine
     * @param TokenStorageInterface  $token_storage
     */
    public function __construct(EntityManager $em, DbalTicketFilterEngine $engine, TokenStorageInterface $token_storage)
    {
        $this->em            = $em;
        $this->engine        = $engine;
        $this->token_storage = $token_storage;
    }

    /**
     * @param TicketFilterSet $set
     * @param array|null      $groupBy
     *
     * @return Count
     */
    public function getFilterSetCount(TicketFilterSet $set, $groupBy = null)
    {
        return $this->getFiltersCount($set->getId(), 'ticket_filter_set', $set->getTitle(), $set->getFilters(), $groupBy);
    }

    /**
     * @param int            $id
     * @param string         $type
     * @param string         $title
     * @param TicketFilter[] $filters
     * @param array|null     $groupBy
     *
     * @return Count
     */
    public function getFiltersCount($id, $type, $title, $filters, $groupBy = null)
    {
        $count   = Count::create(0, $id, $type, $title);
        $groupBy = $groupBy ?: [];

        foreach ($filters as $filter) {
            $filter_group_by = !empty($groupBy[$filter->getId()]) ? $groupBy[$filter->getId()] : null;
            $count->addNestedInstance($this->getFilterCount($filter,  $filter_group_by), true);
        }

        return $count;
    }

    /**
     * @param TicketFilter $filter
     * @param string       $groupBy
     *
     * @return Count
     */
    public function getFilterCount(TicketFilter $filter, $groupBy = null)
    {
        $context = new TermEngineContext($this->getUser());
        if ($groupBy) {
            $context->addGroupByFromString($groupBy);
        }

        /** @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery $tickets_query */
        $tickets_query = $this->engine->evaluate($filter, $context);

        if ($groupBy) {
            $filter_count = Count::create(0, $filter->getId(), 'filter', $filter->getTitle());

            // If grouping by a custom field, then additionally query for total count (w/o grouping)
            // to determine count of tickets where no value set on the custom field
            $total     = 0;
            $is_custom = TicketGrouping::isCustom($groupBy);
            if ($is_custom) {
                $total = $this->engine->evaluate($filter, new TermEngineContext($this->getUser()))->fetchCount();
            }

            $filter_counts = $tickets_query->fetchGroupedCount();
            foreach ($filter_counts as $nested_count) {
                $value = $nested_count['count'];
                unset($nested_count['count']);
                $group = array_pop($nested_count);
                $group = ctype_digit($group) ? (int) $group : $group;

                $filter_count->addNestedInstance(
                    Count::create($value, $group, $groupBy, $this->getTitle($group, $groupBy), null, []),
                    true
                );

                if ($is_custom) {
                    $total -= $value;
                }
            }

            if ($is_custom) {
                $filter_count->addNestedInstance(
                    Count::create($total, null, $groupBy, null, null, []),
                    true
                );
            }

            return $filter_count;
        } else {
            return Count::create(
                $tickets_query->fetchCount(),
                $filter->getId(),
                'filter',
                $filter->getTitle(),
                null,
                []
            );
        }
    }

    /**
     * @param int    $id
     * @param string $type
     *
     * @throws \Exception
     *
     * @return string
     */
    private function getTitle($id, $type)
    {
        if (is_null($id)) {
            return '';
        }
        if (TicketGrouping::isCustom($type)) {
            return $id;
        }

        switch ($type) {
            case TicketGrouping::DEPARTMENT:
                return $this->em->find(Department::class, $id)->getTitle();
            case TicketGrouping::LANGUAGE:
                return $this->em->find(Language::class, $id)->getTitle();
            case TicketGrouping::ORGANIZATION:
                return $this->em->find(Organization::class, $id)->getName();
            case TicketGrouping::AGENT:
            case TicketGrouping::PERSON:
                return $this->em->find(Person::class, $id)->getName();
            case TicketGrouping::AGENT_TEAM:
                return $this->em->find(AgentTeam::class, $id)->getName();
            case TicketGrouping::OPEN_TIME:
            case TicketGrouping::WAITING_TIME:
            case TicketGrouping::ALL_WAITING_TIME:
            case TicketGrouping::URGENCY:
            case TicketGrouping::DATE_CREATED:
                return $id;
            default:
                throw new \Exception("Unknown type '$type'");
        }
    }

    /**
     * @return Person
     */
    private function getUser()
    {
        return $this->token_storage->getToken()->getUser();
    }
}
