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
