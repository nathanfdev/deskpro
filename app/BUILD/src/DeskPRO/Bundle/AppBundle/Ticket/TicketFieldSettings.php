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
     * @return bool
     */
    public function isProductRequired()
    {
        return $this->hasSetting('core_tickets.field_validation_ticket_prod_agent_required');
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
     * @return bool
     */
    public function isPriorityRequired()
    {
        return $this->hasSetting('core_tickets.field_validation_ticket_pri_agent_required');
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
     * @return bool
     */
    public function isCategoryRequired()
    {
        return $this->hasSetting('core_tickets.field_validation_ticket_cat_agent_required');
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
     * @return bool
     */
    public function isWorkflowRequired()
    {
        return $this->hasSetting('core_tickets.field_validation_ticket_work_agent_required');
    }

    /**
     * @param $name
     *
     * @return bool
     */
    private function hasSetting($name)
    {
        return (bool) $this->settings_resolver->getSetting($name, false);
    }
}
