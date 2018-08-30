<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\ExternalEvent;

use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;
use DeskPRO\Component\Util\RandUtils;
use JMS\Serializer\Annotation as JMS;

/**
 * Class PopupEvent.
 *
 * @JMS\ExclusionPolicy("all")
 */
class PopupEvent extends AbstractSystemEvent
{
    const EVENT_NAME = 'external_event.popup';

    const ACTION_TYPE_RAISE   = 'raise';
    const ACTION_TYPE_DISMISS = 'dismiss';

    private $action = null;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    private $data = [];

    /**
     * PopupEvent constructor.
     *
     * @param string $action
     * @param array  $data
     */
    public function __construct($action, array $data)
    {
        $this->uuid   = RandUtils::uuidV4();
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
