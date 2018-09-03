<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\ExternalEvent;

use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;

/**
 * Class PopupEvent.
 */
class PopupEvent extends AbstractSystemEvent
{
    const EVENT_NAME = 'external_event.popup';

    const ACTION_TYPE_RAISE   = 'raise';
    const ACTION_TYPE_DISMISS = 'dismiss';

    private $action = null;

    /**
     * @var string
     */
    private $uuid;

    private $data = [];

    /**
     * PopupEvent constructor.
     *
     * @param string $uuid
     * @param string $action
     * @param array  $data
     */
    public function __construct($uuid, $action, array $data)
    {
        $this->uuid   = $uuid;
        $this->data   = $data;
        $this->action = $action;
    }

    public function __sleep()
    {
        return [
            'action',
            'data',
            'uuid',
        ];
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
}
