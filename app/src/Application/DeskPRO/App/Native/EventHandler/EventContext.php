<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\EventHandler;

use Application\DeskPRO\App\Native\NativeApp;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class EventContext
{
    /**
     * @var string
     */
    private $event_id;

    /**
     * @var array
     */
    private $event_data;

    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @var \Application\DeskPRO\App\Native\NativeApp
     */
    private $native_app;

    /**
     * @param string           $event_id
     * @param array            $event_data
     * @param DeskproContainer $container
     * @param NativeApp        $native_app
     */
    public function __construct($event_id, array $event_data, DeskproContainer $container, NativeApp $native_app)
    {
        $this->event_id   = $event_id;
        $this->event_data = $event_data;
        $this->container  = $container;
        $this->native_app = $native_app;
    }

    /**
     * Given an event ID, check to see if this event is of this type. This uses dot notation to separate
     * type types into hierarchies. E.g., 'my.event.type' would match 'my' and 'my.event' but not 'my.otherevent'. 'my' would match all three.
     *
     * @param  string $event_id
     * @return true
     */
    public function isEventType($event_id)
    {
        $event_id = trim($event_id, '.');

        return strpos($this->event_id.'.', $event_id.'.') === 0;
    }

    /**
     * @return string
     */
    public function getEventId()
    {
        return $this->event_id;
    }

    /**
     * @return array
     */
    public function getEventData()
    {
        return $this->event_data;
    }

    /**
     * @return \Application\DeskPRO\Entity\AppInstance
     */
    public function getNativeApp()
    {
        return $this->native_app;
    }

    /**
     * @return \Application\DeskPRO\Entity\AppInstance
     */
    public function getApp()
    {
        return $this->native_app->getApp();
    }

    /**
     * @return \Application\DeskPRO\Entity\AppPackage
     */
    public function getPackage()
    {
        return $this->native_app->getPackage();
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Application\DeskPRO\DBAL\Connection
     */
    public function getDb()
    {
        return $this->container->getDb();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->container->getEm();
    }
}
