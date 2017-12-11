<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
