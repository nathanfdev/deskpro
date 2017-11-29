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

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity;

class TicketModel
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var PersonModel|null
     */
    public $person = null;

    /**
     * @var OrgModel|null
     */
    public $organization = null;

    /**
     * @var string
     */
    public $status = 'awaiting_agent';

    /**
     * @var int
     */
    public $department = 0;

    /**
     * @var int
     */
    public $agent = 0;

    /**
     * @var int
     */
    public $agent_team = 0;

    /**
     * @var int
     */
    public $language = 0;

    /**
     * @var int
     */
    public $email_account = 0;

    /**
     * @var int[]
     */
    public $followers = [];

    /**
     * @var int
     */
    public $product = 0;

    /**
     * @var int
     */
    public $category = 0;

    /**
     * @var int
     */
    public $priority = 0;

    /**
     * @var int
     */
    public $urgency = 0;

    /**
     * @var int
     */
    public $workflow = 0;

    /**
     * @var string[]
     */
    public $labels = [];

    /**
     * @var bool
     */
    public $is_hold = false;

    /**
     * @var \DateTime|null
     */
    public $date_created = null;

    /**
     * @var \DateTime|null
     */
    public $date_last_agent_reply = null;

    /**
     * @var \DateTime|null
     */
    public $date_last_user_reply = null;

    /**
     * @var \DateTime|null
     */
    public $date_agent_waiting = null;

    /**
     * @var \DateTime|null
     */
    public $date_user_waiting = null;

    /**
     * @var int[]
     */
    public $slas = [];

    /**
     * @var CustomData[]
     */
    public $custom_fields = [];
}
