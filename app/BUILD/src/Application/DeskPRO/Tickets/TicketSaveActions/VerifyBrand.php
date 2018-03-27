<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\Brand as BrandRepository;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class VerifyBrand implements TicketSaveActionInterface
{
    /**
     * @var BrandRepository
     */
    private $brandRepository;

    /**
     * @var
     */
    private $defaultBrand;

    /**
     * @param BrandRepository $brandRepository
     * @param int             $defaultBrandId
     */
    public function __construct(BrandRepository $brandRepository, $defaultBrandId)
    {
        $this->brandRepository = $brandRepository;
        $this->defaultBrand    = $brandRepository->find($defaultBrandId);
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if ($context->getEventType() == 'noop') {
            return;
        }

        if (!$ticket->brand) {
            /** @var Brand $brand */
            if ($brand = $this->defaultBrand) {
                $context->getLogger()->info("Setting system default brand: {$brand->getId()} {$brand->getName()}");
                $ticket->setBrand($brand);
            }
        }
    }
}
