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

use Application\DeskPRO\Entity\Person;
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
                return MapUtils::rekeyByGetter(
                    $this->container
                        ->get('doctrine.orm.entity_manager')
                        ->getRepository(Person::class)
                        ->getByIds($values),
                    'getId'
                );
                break;

            case TicketSearchParams::GROUP_AGENT_TEAM:

                break;

            case TicketSearchParams::GROUP_DEPARTMENT:

                break;

            case TicketSearchParams::GROUP_WORKFLOW:

                break;

            case TicketSearchParams::GROUP_PRIORITY:

                break;

            case TicketSearchParams::GROUP_CATEGORY:

                break;

            case TicketSearchParams::GROUP_PRODUCT:

                break;

            case TicketSearchParams::GROUP_LANGUAGE:

                break;

            default:
                return [];
        }
    }
}
