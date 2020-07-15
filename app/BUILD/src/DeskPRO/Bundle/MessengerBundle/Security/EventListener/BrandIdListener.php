<?php

namespace DeskPRO\Bundle\MessengerBundle\Security\EventListener;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class BrandIdListener
{
    const BRAND_HEADER_NAME = 'X-Deskpro-BrandID';

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(BrandStack $brandStack, EntityManager $entityManager)
    {
        $this->em         = $entityManager;
        $this->brandStack = $brandStack;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request  = $event->getRequest();
        if ($request->headers->has(self::BRAND_HEADER_NAME)) {
            if ($brand = $this->em->find(Brand::class, (int) $request->headers->get(self::BRAND_HEADER_NAME))) {
                $this->brandStack->push($brand);
            }
        }
    }
}
