<?php

namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchContext;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UsersourceListener.
 */
class UsersourceListener implements EventSubscriberInterface
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
            QuickSearchEvents::SEARCH => 'onSearch',
        ];
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onSearch(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $request = $event->getRequest();

        if (!$context->isPerson() || !$request->isValidEmail()) {
            return;
        }

        /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
        $personRepo = $this->em->getRepository(Person::class);
        $person     = $personRepo->findOneByEmail($request->getQuery());

        if (!$person) {
            return;
        }

        if (!$person->isAgent() && $context->getType() === QuickSearchContext::TYPE_AGENT) {
            return;
        }

        $context->addEntity($person);
    }
}
