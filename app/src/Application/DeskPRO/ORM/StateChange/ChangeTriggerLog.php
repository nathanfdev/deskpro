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

namespace Application\DeskPRO\ORM\StateChange;

class ChangeTriggerLog implements ChangeInterface, NonStateTrackingInterface
{
    /**
     * @var string
     */
    private $field_id;

    /**
     * @var string
     */
    private $trigger_id;

    /**
     * @var string
     */
    private $trigger_title;


    /**
     * @param $field_id
     * @param $trigger_id
     * @param $trigger_title
     */
    public function __construct($field_id, $trigger_id, $trigger_title)
    {
        $this->field_id      = $field_id;
        $this->trigger_id    = $trigger_id;
        $this->trigger_title = $trigger_title;
    }


    /**
     * @return string
     */
    public function getField()
    {
        return $this->field_id;
    }


    /**
     * @return array
     */
    public function getOld()
    {
        return null;
    }


    /**
     * @return array
     */
    public function getNew()
    {
        return array(
            'field_id'      => $this->field_id,
            'trigger_id'    => $this->trigger_id,
            'trigger_title' => $this->trigger_title,
        );
    }

    /**
     * @return string
     */
    public function getTriggerId()
    {
        return $this->trigger_id;
    }

    /**
     * @return string
     */
    public function getTriggerTitle()
    {
        return $this->trigger_title;
    }

    /**
     * @return bool
     */
    public function isSame()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isCollection()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isEntity()
    {
        return false;
    }
}
