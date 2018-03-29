<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event;

use Application\DeskPRO\Entity\Person;

/**
 * Class LegacySystemEvent.
 */
class LegacySystemEvent extends AbstractLegacyEvent
{
    const EVENT_NAME = 'system.event';

    /**
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param string $type
     * @param array  $data
     */
    public function __construct($type, array $data = [])
    {
        parent::__construct($type);
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getEventType() ?: parent::getName();
    }

    /**
     * @return int[]
     */
    public function getTargets()
    {
        $targets = [];
        if (isset($this->data['target'])) {
            $targets = $this->transformTarget($this->data['target']);
        }

        return $targets;
    }

    /**
     * @return int[]
     */
    public function getExcludeTargets()
    {
        $targets = [];
        if (isset($this->data['exclude_target'])) {
            $targets = $this->transformTarget($this->data['exclude_target']);
        }

        return $targets;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array_merge($this->data, ['eventType' => $this->eventType]);
    }

    /**
     * @return mixed
     */
    public function __sleep()
    {
        return array_merge(parent::__sleep(), ['data', 'eventType']);
    }

    /**
     * @param mixed $target
     *
     * @return array
     */
    protected function transformTarget($target)
    {
        $targets = [];

        // todo target should accept only scalar values because the event can be persisted, see __sleep method
        if ($target instanceof Person) {
            $targets = [$target->getId()];
        } elseif (is_scalar($target)) {
            $targets = [(int) $target];
        } elseif (is_array($target)) {
            $targets = array_map('intval', $target);
        }

        return $targets;
    }
}
