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

namespace Orb\Jira\Entity;

use Orb\Jira\Entity;

/**
 * The IssueType Class
 * Emulates a Jira issue type.
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class IssueType extends Entity
{
    /** is it a subtask? */
    protected $_subtask = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $params = array())
    {
        if (!isset($params['id']) || !isset($params['name'])) {
            return false;
        }

        $this->_id = $params['id'];

        $this->setName($params['name']);

        if (isset($params['description'])) {
            $this->setDescription($params['description']);
        }

        if (isset($params['subtask']) && $params['subtask']) {
            $this->setSubTask();
        }

        if (isset($params['iconUrl'])) {
            $this->addAvatar($params['iconUrl']);
        }

        return $this;
    }

    /**
     * Checks if it's a subtask.
     *
     * @return bool "TRUE" if it's a subtask and "FALSE" otherwise
     */
    public function isSubTask()
    {
        return $this->_subtask;
    }

    /**
     * Sets this to be a subtask.
     *
     * @return \Orb\Jira\Entity\IssueType
     */
    public function setSubTask()
    {
        $this->_subtask = true;

        return $this;
    }
}
