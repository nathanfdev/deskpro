<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\PortalBundle\Brand\DefaultBrandFinder;
use Doctrine\ORM\EntityManager;

/**
 * Class BrandListener.
 */
class BrandListener
{
    /**
     * @var DefaultBrandFinder
     */
    private $defaultBrandFinder;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param DefaultBrandFinder $defaultBrandFinder
     * @param EntityManager      $em
     */
    public function __construct(DefaultBrandFinder $defaultBrandFinder, EntityManager $em)
    {
        $this->defaultBrandFinder = $defaultBrandFinder;
        $this->em                 = $em;
    }

    /**
     * @param Brand $entity
     */
    public function preRemove(Brand $entity)
    {
        $defaultBrand = $this->defaultBrandFinder->getDefaultBrand();
        if (!$defaultBrand) {
            return;
        }

        $defaultTicketDepartments = $defaultBrand->getTicketDepartments();
        $defaultChatDepartments   = $defaultBrand->getChatDepartments();

        // re-assign related tickets to the default brand
        if (count($defaultTicketDepartments)) {
            // just change brand w/o changing department
            $qb = $this->em->createQueryBuilder();
            $qb
                ->update(Ticket::class, 't')
                ->set('t.brand', ':default_brand')
                ->where(
                    't.brand = :deleting_brand',
                    't.department IN (:default_departments)'
                )
                ->setParameter('default_brand', $defaultBrand)
                ->setParameter('deleting_brand', $entity)
                ->setParameter('default_departments', $defaultTicketDepartments)
                ->getQuery()
                ->execute()
            ;
        }

        // modify department to first default one as well
        $defaultDepartment = $defaultTicketDepartments->first();
        if ($defaultDepartment) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->update(Ticket::class, 't')
                ->set('t.brand', ':default_brand')
                ->set('t.department', ':default_department')
                ->where('t.brand = :deleting_brand')
                ->setParameter('default_brand', $defaultBrand)
                ->setParameter('deleting_brand', $entity)
                ->setParameter('default_department', $defaultTicketDepartments->first())
                ->getQuery()
                ->execute()
            ;
        }

        // re-assign related chat to the default brand
        if (count($defaultChatDepartments)) {
            // just change brand w/o changing department
            $qb = $this->em->createQueryBuilder();
            $qb
                ->update(ChatConversation::class, 'c')
                ->set('c.brand', ':default_brand')
                ->where(
                    'c.brand = :deleting_brand',
                    'c.department IN (:default_departments)'
                )
                ->setParameter('default_brand', $defaultBrand)
                ->setParameter('deleting_brand', $entity)
                ->setParameter('default_departments', $defaultChatDepartments)
                ->getQuery()
                ->execute()
            ;
        }

        // modify department to first default one as well
        $defaultDepartment = $defaultChatDepartments->first();
        if ($defaultDepartment) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->update(ChatConversation::class, 'c')
                ->set('c.brand', ':default_brand')
                ->set('c.department', ':default_department')
                ->where('c.brand = :deleting_brand')
                ->setParameter('default_brand', $defaultBrand)
                ->setParameter('deleting_brand', $entity)
                ->setParameter('default_department', $defaultDepartment)
                ->getQuery()
                ->execute()
            ;
        }
    }
}
