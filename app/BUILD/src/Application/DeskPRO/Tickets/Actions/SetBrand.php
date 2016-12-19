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
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Set the brand.
 *
 * @option int brand_id
 */
class SetBrand extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addRequiredNames('brand_id');

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $setBrandId = $this->getActionOption('brand_id');
        /** @var Brand $brand */
        $brand = $this->getContainer()->getEm()->getRepository(Brand::class)->find($setBrandId);

        if (!$brand) {
            return;
        }

        $ticket->setBrand($brand);
        $context->getLogger()->debug(sprintf('[SetBrand] Setting %d %s', $brand->getId(), $brand->getName()));

        // Tmp hack until can figure out why this isn't persisted on at least one server
        // IIS, PHP 5.4.24, WinCache 1.3.4.0
        if (isset(App::$container)) { // if is to prevent this during testing
            App::$container->getDb()->executeUpdate('UPDATE tickets SET brand_id = ? WHERE id = ?',
                [$brand->getId(), $ticket->getId()]);
            App::$container->getDb()->executeUpdate('UPDATE tickets_search_active SET brand_id = ? WHERE id = ?',
                [$brand->getId(), $ticket->getId()]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
    {
        $setBrandId    = $this->getActionOption('brand_id');
        $ticketBrandId = $ticket->getBrand() ? $ticket->getBrand()->getId() : 0;

        if ($ticketBrandId == $setBrandId) {
            $context->getLogger()->debug('[SetBrand] Skipping, brand is the same');

            return true;
        }

        if (!$this->getContainer()->getEm()->getRepository(Brand::class)->find($setBrandId)) {
            $context->getLogger()->debug('[SetBrand] Unknown brand id: '.$setBrandId);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'brand')) {
            return ['brand'];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}
