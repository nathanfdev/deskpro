<?php

namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchContext;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use DeskPRO\Component\Util\RegexUtils;
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
            ->from(Ticket::class, 't')
            ->setMaxResults($request->getLimit())
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
            $context->addId($result['id']);
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
            QuickSearchContext::TYPE_TOPIC,
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
            $context->addId($result['id']);
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

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('p')
            ->from(Person::class, 'p')
            ->join('p.emails', 'pe')
            ->setMaxResults(15)
            ->orderBy('p.id', 'desc')
        ;

        if ($request->getParam('with_phone_number')) {
            $qb->join('p.phone_numbers', 'pn');
        } else {
            $qb->leftJoin('p.phone_numbers', 'pn');
        }

        if ($request->isEmailPart()) {
            if ($request->isEmailDomain()) {
                $qb->andWhere('pe.email_domain LIKE :email_domain');
                $qb->setParameter('email_domain', $this->escapeLike($request->getEmailDomain()).'%');
            } else {
                $qb->andWhere('pe.email LIKE :email');
                $qb->setParameter('email', $this->escapeLike($request->getQuery()).'%');
            }
        } else {
            $qb
                ->andWhere($qb->expr()->orX(
                    'p.name LIKE :query',
                    'p.first_name LIKE :query',
                    'p.last_name LIKE :query',
                    'pe.email LIKE :query',
                    'pn.number LIKE :query',
                    "CONCAT(CONCAT(p.first_name, ' '), p.last_name) LIKE :query"
                ))
                ->setParameter('query', '%'.$this->escapeLike(RegexUtils::safePregReplace('#\s+#', ' ', $request->getQuery())).'%')
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

        $options = $context->getCriteriaOptions();
        if (isset($options['is_agent'])) {
            $qb->andWhere('p.is_agent = :is_agent');
            $qb->setParameter('is_agent', $options['is_agent']);
        }

        /** @var \Application\DeskPRO\Entity\Person[] $people */
        $people = $qb->getQuery()->getResult();
        foreach ($people as $person) {
            $context->addEntity($person);
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
        $repository    = $this->em->getRepository(Organization::class);
        $organizations = $repository->search($request->getQuery(), 25);

        foreach ($organizations as $organization) {
            $context->addEntity($organization);
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
            QuickSearchContext::TYPE_AGENT,
            QuickSearchContext::TYPE_TICKET,
        ];

        $context = $event->getContext();
        $request = $event->getRequest();

        if (!in_array($context->getType(), $types)) {
            return;
        }

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('t.id')
            ->from($context->getEntityName(), 't')
            ->join('t.labels', 'l')
            ->where('l.label = :label')
            ->setParameter('label', $request->getLabel() ?: $request->getQuery())
        ;

        switch ($context->getType()) {
            case QuickSearchContext::TYPE_TICKET:
                $qb->andWhere("t.status IN('awaiting_agent', 'awaiting_user', 'archived', 'resolved')");
                break;
            case QuickSearchContext::TYPE_ARTICLE:
            case QuickSearchContext::TYPE_DOWNLOAD:
            case QuickSearchContext::TYPE_FEEDBACK:
            case QuickSearchContext::TYPE_NEWS:
            case QuickSearchContext::TYPE_TOPIC:
                $qb->andWhere($qb->expr()->orX(
                    "t.hidden_status NOT IN('spam', 'deleted')",
                    't.hidden_status is null'
                ));
                break;
            case QuickSearchContext::TYPE_AGENT:
                $qb->andWhere('t.is_agent = :is_agent');
                $qb->setParameter('is_agent', true);
                break;
            default:
                break;
        }

        $results = $qb->getQuery()->getScalarResult();
        foreach ($results as $result) {
            $context->addId($result['id']);
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
            ->from(Ticket::class, 't')
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
