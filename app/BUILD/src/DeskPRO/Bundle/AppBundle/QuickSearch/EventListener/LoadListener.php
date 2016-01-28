<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchContext;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class LoadListener.
 */
class LoadListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuickSearchEvents::POST_SEARCH => [
                ['onLoadEntities', 1000], // Load entities before permission check
                ['onLoadPeopleOrganizations', 999],
                ['onLoadOrganizationsPeople', 998],
            ],
        ];
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onLoadEntities(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $ids     = $context->getDeferredIds();

        if (empty($ids)) {
            return;
        }

        $entities = $this->em->getRepository($context->getEntityName())->findBy(['id' => $ids]);
        foreach ($entities as $entity) {
            $context->addEntity($entity);
        }
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onLoadPeopleOrganizations(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        if (!$context->isPerson()) {
            return;
        }

        /** @var \Application\DeskPRO\Entity\Person[] $people */
        $people = $context->getEntities();
        foreach ($people as $person) {
            $organization = $person->getOrganization();
            if ($organization) {
                $organization_context = $context->getResponse()->getContext(QuickSearchContext::TYPE_ORGANIZATION);
                $organization_context->addEntity($organization);
            }
        }
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onLoadOrganizationsPeople(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        if (!$context->isOrganization()) {
            return;
        }

        /** @var \Application\DeskPRO\Entity\Organization[] $organizations */
        $organizations = $context->getEntities();

        $ids = [];
        foreach ($organizations as $organization) {
            $context->addEntity($organization);
            $ids[] = $organization->getId();
        }

        // Fetch users of these organizations too
        if (!empty($ids)) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('p')
                ->from('DeskPRO:Person', 'p')
                ->where('p.organization IN(:ids)')
                ->orderBy('p.date_last_login', 'desc')
                ->setMaxResults(100)
                ->setParameter('ids', $ids)
            ;

            /** @var \Application\DeskPRO\Entity\Person[] $people */
            $people = $qb->getQuery()->getResult();
            foreach ($people as $person) {
                $person_context = $context->getResponse()->getContext(QuickSearchContext::TYPE_PERSON);
                $person_context->addEntity($person);
            }
        }
    }
}
