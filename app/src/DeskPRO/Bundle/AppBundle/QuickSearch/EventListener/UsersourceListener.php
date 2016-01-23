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

use Application\DeskPRO\Usersource\UsersourceManager;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchContext;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UsersourceListener.
 */
class UsersourceListener implements EventSubscriberInterface
{
    /**
     * @var UsersourceManager
     */
    private $usersource_manager;

    /**
     * Constructor.
     *
     * @param UsersourceManager $usersource_manager
     */
    public function __construct(UsersourceManager $usersource_manager)
    {
        $this->usersource_manager = $usersource_manager;
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
        if ($context->getType() !== QuickSearchContext::TYPE_PERSON) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isEmail()) {
            return;
        }

        $person = $this->usersource_manager->findPersonByEmail($request->getQuery());
        if ($person) {
            $context->ids->add($person->getId());
            $context->entities->add($person);
        }
    }
}
