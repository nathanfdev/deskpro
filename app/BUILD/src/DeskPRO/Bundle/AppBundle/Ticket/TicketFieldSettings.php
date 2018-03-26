<?php

namespace DeskPRO\Bundle\AppBundle\Ticket;

use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketWorkflow;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use Doctrine\ORM\EntityManager;

/**
 * Class TicketSettings.
 */
class TicketFieldSettings
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var BrandAwareSettingsResolver
     */
    private $settings_resolver;

    /**
     * Constructor.
     *
     * @param EntityManager              $em
     * @param BrandAwareSettingsResolver $settings_resolver
     */
    public function __construct(EntityManager $em, BrandAwareSettingsResolver $settings_resolver)
    {
        $this->em                = $em;
        $this->settings_resolver = $settings_resolver;
    }

    /**
     * @return bool
     */
    public function canProductBeDisplayed()
    {
        if (!$this->hasSetting('core.use_product')) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\Product $repository */
        $repository = $this->em->getRepository(Product::class);

        return $repository->countAll() > 0;
    }

    /**
     * @param bool $agent
     *
     * @return bool
     */
    public function isProductRequired($agent = false)
    {
        $prefix = $agent ? 'agent' : 'user';

        return $this->hasSetting("core_tickets.field_validation_ticket_prod_{$prefix}_required");
    }

    /**
     * @return bool
     */
    public function canPriorityBeDisplayed()
    {
        if (!$this->hasSetting('core.use_ticket_priority')) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketPriority $repository */
        $repository = $this->em->getRepository(TicketPriority::class);

        return $repository->countAll() > 0;
    }

    /**
     * @param bool $agent
     *
     * @return bool
     */
    public function isPriorityRequired($agent = false)
    {
        $prefix = $agent ? 'agent' : 'user';

        return $this->hasSetting("core_tickets.field_validation_ticket_pri_{$prefix}_required");
    }

    /**
     * @return bool
     */
    public function canCategoryBeDisplayed()
    {
        if (!$this->hasSetting('core.use_ticket_category')) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketCategory $repository */
        $repository = $this->em->getRepository(TicketCategory::class);

        return $repository->countAll() > 0;
    }

    /**
     * @param bool $agent
     *
     * @return bool
     */
    public function isCategoryRequired($agent = false)
    {
        $prefix = $agent ? 'agent' : 'user';

        return $this->hasSetting("core_tickets.field_validation_ticket_cat_{$prefix}_required");
    }

    /**
     * @return bool
     */
    public function canWorkflowBeDisplayed()
    {
        if (!$this->hasSetting('core.use_ticket_workflow')) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketWorkflow $repository */
        $repository = $this->em->getRepository(TicketWorkflow::class);

        return $repository->countAll() > 0;
    }

    /**
     * @param bool $agent
     *
     * @return bool
     */
    public function isWorkflowRequired($agent = false)
    {
        $prefix = $agent ? 'agent' : 'user';

        return $this->hasSetting("core_tickets.field_validation_ticket_work_{$prefix}_required");
    }

    /**
     * @param $name
     *
     * @return bool
     */
    private function hasSetting($name)
    {
        return (bool) $this->settings_resolver->getSetting($name);
    }
}
