<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\CustomDefTicket;
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
     * @var EnvLoader
     */
    private $loader;

    /**
     * TicketCountTitleResolver constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container, EnvLoader $loader)
    {
        $this->container = $container;
        $this->loader    = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitles($fieldId, array $values)
    {
        $fieldInfo = TicketSearchParams::parseFieldId($fieldId);

        switch ($fieldInfo['type']) {
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

            case TicketSearchParams::GROUP_TICKET_FIELD_PREFIX:
                $ticketFieldId = (int) $fieldInfo['name'];

                $field = $this->container->get('doctrine.orm.entity_manager')->find(CustomDefTicket::class, $ticketFieldId);

                if (!$field) {
                    return [];
                }

                $titles     = $field->getAllChildTitles();
                $titles[-1] = 'None';

                return $titles;

            default:
                return [];
        }
    }
}
