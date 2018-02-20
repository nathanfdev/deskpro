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

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketWorkflow;
use DeskPRO\Bundle\AppBundle\CountBadge\CountTitleResolver;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Component\DependencyInjection\Container;

class TicketCountTitleResolver implements CountTitleResolver
{
    /**
     * @var Container
     */
    private $container;

    /**
     * TicketCountTitleResolver constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitles($fieldId, array $values)
    {
        switch ($fieldId) {
            case TicketSearchParams::GROUP_SLA_SEVERITY:
                return ['ok' => 'Ok', 'fail' => 'Fail', 'warn' => 'Warning'];
                break;

            case TicketSearchParams::GROUP_AGENT:
                return MapUtils::map(
                    $this->container
                        ->get('doctrine.orm.entity_manager')
                        ->getRepository(Person::class)
                        ->getByIds($values),
                    function ($idx, Person $a) {
                        return [$a->getId(), $a->getName()];
                    }
                );
                break;

            case TicketSearchParams::GROUP_AGENT_TEAM:
                return MapUtils::map(
                    $this->container
                        ->get('doctrine.orm.entity_manager')
                        ->getRepository(AgentTeam::class)
                        ->getByIds($values),
                    function ($idx, AgentTeam $a) {
                        return [$a->getId(), $a->getName()];
                    }
                );
                break;

            case TicketSearchParams::GROUP_DEPARTMENT:
                return MapUtils::map(
                    $this->container
                        ->get('doctrine.orm.entity_manager')
                        ->getRepository(Department::class)
                        ->getByIds($values),
                    function ($idx, Department $a) {
                        return [$a->getId(), $a->getFullTitle()];
                    }
                );
                break;

            case TicketSearchParams::GROUP_WORKFLOW:
                return MapUtils::map(
                    $this->container
                        ->get('doctrine.orm.entity_manager')
                        ->getRepository(TicketWorkflow::class)
                        ->getByIds($values),
                    function ($idx, TicketWorkflow $a) {
                        return [$a->getId(), $a->getTitle()];
                    }
                );
                break;

            case TicketSearchParams::GROUP_PRIORITY:
                return MapUtils::map(
                    $this->container
                        ->get('doctrine.orm.entity_manager')
                        ->getRepository(TicketPriority::class)
                        ->getByIds($values),
                    function ($idx, TicketPriority $a) {
                        return [$a->getId(), $a->getTitle()];
                    }
                );
                break;

            case TicketSearchParams::GROUP_CATEGORY:
                return MapUtils::map(
                    $this->container
                        ->get('doctrine.orm.entity_manager')
                        ->getRepository(TicketCategory::class)
                        ->getByIds($values),
                    function ($idx, TicketCategory $a) {
                        return [$a->getId(), $a->getTitle()];
                    }
                );
                break;

            case TicketSearchParams::GROUP_PRODUCT:
                return MapUtils::map(
                    $this->container
                        ->get('doctrine.orm.entity_manager')
                        ->getRepository(Product::class)
                        ->getByIds($values),
                    function ($idx, Product $a) {
                        return [$a->getId(), $a->getTitle()];
                    }
                );
                break;

            case TicketSearchParams::GROUP_LANGUAGE:
                return MapUtils::map(
                    $this->container
                        ->get('doctrine.orm.entity_manager')
                        ->getRepository(Language::class)
                        ->getByIds($values),
                    function ($idx, Language $a) {
                        return [$a->getId(), $a->getTitle()];
                    }
                );
                break;

            default:
                return [];
        }
    }
}
