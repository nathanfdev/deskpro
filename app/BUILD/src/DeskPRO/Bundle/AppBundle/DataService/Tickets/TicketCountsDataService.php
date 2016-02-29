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
     * @param array|null      $group_by
     *
     * @return Count
     */
    public function getFilterSetCount(TicketFilterSet $set, $group_by = null)
    {
        return $this->getFiltersCount($set->getId(), 'ticket_filter_set', $set->getTitle(), $set->getFilters(), $group_by);
    }

    /**
     * @param int            $id
     * @param string         $type
     * @param string         $title
     * @param TicketFilter[] $filters
     * @param array|null     $group_by
     *
     * @return Count
     */
    public function getFiltersCount($id, $type, $title, $filters, $group_by = null)
    {
        $count    = Count::create(0, $id, $type, $title);
        $group_by = $group_by ?: [];

        foreach ($filters as $filter) {
            $filter_group_by = !empty($group_by[$filter->getId()]) ? $group_by[$filter->getId()] : null;
            $count->addNestedInstance($this->getFilterCount($filter,  $filter_group_by), true);
        }

        return $count;
    }

    /**
     * @param TicketFilter $filter
     * @param string       $group_by
     *
     * @return Count
     */
    public function getFilterCount(TicketFilter $filter, $group_by = null)
    {
        $context = new TermEngineContext($this->getUser());
        if ($group_by) {
            $context->addGroupByFromString($group_by);
        }

        /** @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalExecutableQuery $tickets_query */
        $tickets_query = $this->engine->evaluate($filter, $context);

        if ($group_by) {
            $filter_count = Count::create(0, $filter->getId(), 'filter', $filter->getTitle());

            // If grouping by a custom field, then additionally query for total count (w/o grouping)
            // to determine count of tickets where no value set on the custom field
            $total     = 0;
            $is_custom = TicketGrouping::isCustom($group_by);
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
                    Count::create($value, $group, $group_by, $this->getTitle($group, $group_by), null, []),
                    true
                );

                if ($is_custom) {
                    $total -= $value;
                }
            }

            if ($is_custom) {
                $filter_count->addNestedInstance(
                    Count::create($total, null, $group_by, null, null, []),
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
            case 'department':
                return $this->em->find(Department::class, $id)->getTitle();
            case 'language':
                return $this->em->find(Language::class, $id)->getTitle();
            case 'organization':
                return $this->em->find(Organization::class, $id)->getName();
            case 'agent':
            case 'person':
                return $this->em->find(Person::class, $id)->getName();
            case 'agent_team':
                return $this->em->find(AgentTeam::class, $id)->getName();
            case 'open_time':
            case 'waiting_time':
            case 'all_waiting_time':
            case 'urgency':
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
