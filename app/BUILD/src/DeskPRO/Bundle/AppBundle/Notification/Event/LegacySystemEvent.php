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

class LegacySystemEvent extends AbstractLegacyEvent
{
    const EVENT_NAME = 'system.event';

    private $data;

    /**
     * AgentStatusChangedEvent constructor.
     *
     * @param string $type
     * @param array  $data
     */
    public function __construct($type, $data)
    {
        parent::__construct($type);
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getTarget()
    {
        return isset($this->data['target']) ? $this->data['target'] : null;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function __sleep()
    {
        return array_merge(parent::__sleep(), ['data']);
    }
}
