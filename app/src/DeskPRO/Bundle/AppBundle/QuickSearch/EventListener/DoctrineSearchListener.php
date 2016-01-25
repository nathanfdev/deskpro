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

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchContext;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DoctrineSearchListener.
 */
class DoctrineSearchListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SettingsResolver
     */
    private $settings_resolver;

    /**
     * Constructor.
     *
     * @param EntityManager    $em
     * @param SettingsResolver $settings_resolver
     */
    public function __construct(EntityManager $em, SettingsResolver $settings_resolver)
    {
        $this->em                = $em;
        $this->settings_resolver = $settings_resolver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuickSearchEvents::SEARCH_FALLBACK => [
                ['onSearchTicketSubjects', 1],
                ['onSearchTitles', 1],
                ['onSearchPeople', 1],
                ['onSearchOrganizations', 1],
                ['onSearchLabels', 1],
            ],
        ];
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onSearchTicketSubjects(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $request = $event->getRequest();
        $words   = $request->getWords();

        if (!$context->isTicket() || empty($words) || $request->isLabel()) {
            return;
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('t.id')
            ->from('DeskPRO:Ticket', 't')
            ->setMaxResults(100)
            ->orderBy('t.id', 'desc')
            ->where('t.id >= :after_id')
            ->setParameter('after_id', $this->getMinTicketId())
        ;

        foreach ($words as $num => $word) {
            $param_id = 'subject_'.$num;
            $qb
                ->andWhere('t.subject LIKE :'.$param_id)
                ->setParameter($param_id, '%'.$this->escapeLike($word).'%')
            ;
        }

        $results = $qb->getQuery()->getScalarResult();
        foreach ($results as $result) {
            $context->ids->add((int) $result['id']);
        }
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onSearchTitles(QuickSearchEvent $event)
    {
        $types = [
            QuickSearchContext::TYPE_ARTICLE,
            QuickSearchContext::TYPE_DOWNLOAD,
            QuickSearchContext::TYPE_FEEDBACK,
            QuickSearchContext::TYPE_NEWS,
        ];

        $context = $event->getContext();
        $request = $event->getRequest();
        $words   = $request->getWords();

        if (!in_array($context->getType(), $types) || empty($words) || $request->isLabel()) {
            return;
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('t.id')
            ->from($context->getEntityName(), 't')
            ->orderBy('t.id', 'desc')
            ->setMaxResults(25)
            ->andWhere("t.status != 'hidden'")
        ;

        foreach ($words as $num => $word) {
            $param_id = 'title_'.$num;
            $qb
                ->andWhere('t.title LIKE :'.$param_id)
                ->setParameter($param_id, '%'.$this->escapeLike($word).'%')
            ;
        }

        $results = $qb->getQuery()->getScalarResult();
        foreach ($results as $result) {
            $context->ids->add($result['id']);
        }
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onSearchPeople(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $request = $event->getRequest();

        // Use usersource listener for valid emails
        if (!$context->isPerson() || $request->isValidEmail() || $request->isLabel()) {
            return;
        }

        $query = $request->getQuery();
        $qb    = $this->em->createQueryBuilder();
        $qb
            ->select('p')
            ->from('DeskPRO:Person', 'p')
            ->join('p.emails', 'pe')
            ->setMaxResults(15)
            ->orderBy('p.id', 'desc')
        ;

        if (strpos($query, '@') !== false) {
            $email     = $query;
            $is_domain = strpos($query, '@') === 0;
            if ($is_domain) {
                $email = substr($email, 1);
            }

            $qb->setParameter('email', $this->escapeLike($email).'%');
            if ($is_domain) {
                $qb->andWhere('pe.email_domain LIKE :email');
            } else {
                $qb->andWhere('pe.email LIKE :email');
            }
        } else {
            $qb
                ->andWhere($qb->expr()->orX(
                    'p.name LIKE :query',
                    'p.first_name LIKE :query',
                    'p.last_name LIKE :query',
                    'pe.email LIKE :query',
                    "CONCAT(CONCAT(p.first_name, ' '), p.last_name) LIKE :query"
                ))
                ->setParameter('query', '%'.$this->escapeLike(preg_replace('#\s+#', ' ', $query)).'%')
            ;
        }

        $people_count = $this->settings_resolver->getGlobalSettings()->get('core_tablecounts.people');
        if ($people_count > 150000) {
            $qb
                ->join('p.tickets', 'tp')
                ->join('tp.ticket', 't')
                ->andWhere($qb->expr()->orX(
                    't.id >= :after_id',
                    'tp.ticket >= :after_id'
                ))
                ->setParameter('after_id', $this->getMinTicketId())
                ->orderBy('t.id', 'desc')
            ;
        }

        /** @var \Application\DeskPRO\Entity\Person[] $people */
        $people = $qb->getQuery()->getResult();
        foreach ($people as $person) {
            $context->ids->add($person->getId());
            $context->entities->add($person);

            $organization = $person->getOrganization();
            if ($organization) {
                $organization_context = $context->getResponse()->getContext(QuickSearchContext::TYPE_ORGANIZATION);
                $organization_context->ids->add($organization->getId());
                $organization_context->entities->add($organization);
            }
        }
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onSearchOrganizations(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $request = $event->getRequest();

        if (!$context->isOrganization() || $request->isLabel()) {
            return;
        }

        /** @var \Application\DeskPRO\EntityRepository\Organization $repository */
        $repository    = $this->em->getRepository('DeskPRO:Organization');
        $organizations = $repository->search($request->getQuery(), 25);

        $ids = [];
        foreach ($organizations as $organization) {
            $context->ids->add($organization->getId());
            $context->entities->add($organization);

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
                $person_context->ids->add($person->getId());
                $person_context->entities->add($person);
            }
        }
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onSearchLabels(QuickSearchEvent $event)
    {
        $types = [
            QuickSearchContext::TYPE_ARTICLE,
            QuickSearchContext::TYPE_DOWNLOAD,
            QuickSearchContext::TYPE_FEEDBACK,
            QuickSearchContext::TYPE_NEWS,
            QuickSearchContext::TYPE_ORGANIZATION,
            QuickSearchContext::TYPE_PERSON,
            QuickSearchContext::TYPE_TICKET,
        ];

        $context = $event->getContext();
        $request = $event->getRequest();

        if (!in_array($context->getType(), $types) || !$request->isLabel()) {
            return;
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('t.id')
            ->from($context->getEntityName(), 't')
            ->join('t.labels', 'l')
            ->where('l.label = :label')
            ->setParameter('label', $request->getLabel())
        ;

        switch ($context->getType()) {
            case QuickSearchContext::TYPE_TICKET:
                $qb->andWhere("t.status IN('awaiting_agent', 'awaiting_user', 'archived', 'resolved')");
                break;
            case QuickSearchContext::TYPE_ARTICLE:
            case QuickSearchContext::TYPE_DOWNLOAD:
            case QuickSearchContext::TYPE_FEEDBACK:
            case QuickSearchContext::TYPE_NEWS:
                $qb->andWhere($qb->expr()->orX(
                    "t.hidden_status NOT IN('spam', 'deleted')",
                    't.hidden_status is null'
                ));
                break;
            default:
                break;
        }

        $results = $qb->getQuery()->getScalarResult();
        foreach ($results as $result) {
            $context->ids->add($result['id']);
        }
    }

    /**
     * @return int
     */
    private function getMinTicketId()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('t.id')
            ->from('DeskPRO:Ticket', 't')
            ->setMaxResults(1)
            ->setFirstResult(10000)
        ;

        $result = $qb->getQuery()->getOneOrNullResult();

        return !empty($result) ? array_shift($result) : 1;
    }

    /**
     * @param string $query
     *
     * @return string
     */
    private function escapeLike($query)
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $query);
    }
}
