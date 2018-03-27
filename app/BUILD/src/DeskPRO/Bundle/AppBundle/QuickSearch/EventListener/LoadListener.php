<?php

namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use Application\DeskPRO\Entity\Person;
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
        if (!$context->isPerson() || !$event->getRequest()->enabledSideloads()) {
            return;
        }

        $organizationContext = $context->getResponse()->getContext(QuickSearchContext::TYPE_ORGANIZATION);
        if (!$organizationContext) {
            return;
        }

        /** @var \Application\DeskPRO\Entity\Person[] $people */
        $people = $context->getEntities();
        foreach ($people as $person) {
            if ($context->isRelatedEntity($person)) {
                continue;
            }

            $organization = $person->getOrganization();
            if ($organization) {
                $organizationContext->addRelatedEntity($organization);
            }
        }
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onLoadOrganizationsPeople(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        if (!$context->isOrganization() || !$event->getRequest()->enabledSideloads()) {
            return;
        }

        $personContext = $context->getResponse()->getContext(QuickSearchContext::TYPE_PERSON);
        $agentContext  = $context->getResponse()->getContext(QuickSearchContext::TYPE_AGENT);
        if (!$personContext && !$agentContext) {
            return;
        }

        /** @var \Application\DeskPRO\Entity\Organization[] $organizations */
        $organizations = $context->getEntities();

        // Fetch users of these organizations too
        // Skip related organizations (which wasn't found via the searcher)
        $ids = [];
        foreach ($organizations as $organization) {
            if (!$context->isRelatedEntity($organization)) {
                $ids[] = $organization->getId();
            }
        }
        if (!empty($ids)) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('p')
                ->from(Person::class, 'p')
                ->where('p.organization IN(:ids)')
                ->orderBy('p.date_last_login', 'desc')
                ->setMaxResults(100)
                ->setParameter('ids', $ids)
            ;

            /** @var \Application\DeskPRO\Entity\Person[] $people */
            $people = $qb->getQuery()->getResult();
            foreach ($people as $person) {
                if ($personContext) {
                    $personContext->addRelatedEntity($person);
                }
                if ($agentContext) {
                    $agentContext->addRelatedEntity($person);
                }
            }
        }
    }
}
