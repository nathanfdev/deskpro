<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PersonBundle\EventListener;


use Application\DeskPRO\Entity\Person;
use Application\PersonBundle\Events\PersonCreateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Responsible for making sure the creation_system is set on a person correctly for every NEW person.
 *
 * Current implementation is that it is generated elsewhere and passed with the context
 */
class CreationSystemListener implements EventSubscriberInterface
{
    public function onPreCreate(PersonCreateEvent $event)
    {
        $event->getPerson()->creation_system = $event->getContext()->getCreationSystem();
    }

    public static function getSubscribedEvents()
    {
        return array(
            Person::EVENT_PRE_CREATE => 'onPreCreate'
        );
    }
}
 