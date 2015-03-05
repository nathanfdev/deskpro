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

namespace Application\DeskPRO\People\AgentNotifPrefs;

use Application\DeskPRO\Entity\TicketFilter;

class Prefs
{
    const TYPE_EMAIL  = 'email';
    const TYPE_ALERT  = 'alert';
    const ALWAYS_SEND = 'always_send';
    const SMART_SEND  = 'smart_send';

    /** @var array */
    private $filter_subs         = array();
    /** @var array */
    private $filter_notify_prefs = array();
    /** @var array */
    private $app_subs            = array();
    /** @var string */
    private $email_mention_mode  = self::ALWAYS_SEND;

    /** @var array */
    public static $apps = array(
        'chat' => 1,
        'task' => 1,
        'twitter' => 1,
        'feedback' => 1,
        'publish' => 1,
        'crm' => 1,
        'account' => 1,
    );

    public function __construct()
    {
        // Init all prefs to off
        // (We dont do this for all filter subs because
        // this class does not know which filters exist)

        $this->filter_notify_prefs = array(
            self::TYPE_EMAIL => array_fill_keys($this->getFilterNotifyPrefNames(self::TYPE_EMAIL), false),
            self::TYPE_ALERT => array_fill_keys($this->getFilterNotifyPrefNames(self::TYPE_ALERT), false),
        );

        foreach (self::$apps as $app_name => $bool) {
            $method = 'get' . ucfirst($app_name) . 'NotifyTypes';

            $this->app_subs[$app_name] = array(
                self::TYPE_EMAIL => array_fill_keys($this->$method(self::TYPE_EMAIL), false),
                self::TYPE_ALERT => array_fill_keys($this->$method(self::TYPE_ALERT), false),
            );
        }
    }


    /**
     * @param  TicketFilter              $filter
     * @param  array                     $sub_types Map of subtype=>value. Currently, value must be either true or false
     * @param  string                    $type
     * @throws \InvalidArgumentException
     */
    public function setFilterSubs($type, TicketFilter $filter, array $sub_types)
    {
        $valid_sub_types = $this->getFilterNotifyTypes($filter, $type);

        if (!isset($this->filter_subs[$type])) {
            $this->filter_subs[$type] = array();
        }

        $this->filter_subs[$type][$filter->id] = array();
        foreach ($valid_sub_types as $sub_type) {
            $this->filter_subs[$type][$filter->id][$sub_type] = false;
        }

        foreach ($sub_types as $sub_type => $value) {
            if (!in_array($sub_type, $valid_sub_types)) {
                continue;
            }

            $this->filter_subs[$type][$filter->id][$sub_type] = (bool)$value;
        }
    }


    /**
     * @param $type
     * @param  TicketFilter $for_filter
     * @return array
     */
    public function getFilterSubsForFilter($type, TicketFilter $for_filter)
    {
        $prefs = $this->getFilterNotifyTypes($for_filter, $type);
        $prefs = array_fill_keys($prefs, false);
        if (isset($this->filter_subs[$type][$for_filter->id])) {
            $prefs = array_merge($prefs, $this->filter_subs[$type][$for_filter->id]);
        }

        return $prefs;
    }


    /**
     * This gets the filter sub settings for all set filters.
     *
     * @param  string|null $type
     * @return array
     */
    public function getFilterSubs($type = null)
    {
        if ($type === null) {
            $ret = array();
            foreach (array('email', 'alert') as $type) {
                foreach ($this->getFilterSubs($type) as $filter_id => $subs) {
                    if (!isset($ret[$filter_id])) {
                        $ret[$filter_id] = array();
                    }

                    foreach ($subs as $s => $v) {
                        if ($v) {
                            $ret[$filter_id]["{$type}_{$s}"] = true;
                        }
                    }
                }
            }

            return $ret;
        }

        if (!isset($this->filter_subs[$type])) {
            return array();
        }

        return $this->filter_subs[$type];
    }


    /**
     * @return string
     */
    public function getEmailMentionMode()
    {
        return $this->email_mention_mode;
    }


    /**
     * @param $mode
     * @throws \InvalidArgumentException
     */
    public function setEmailMentionMode($mode)
    {
        if ($mode != self::ALWAYS_SEND && $mode != self::SMART_SEND) {
            throw new \InvalidArgumentException();
        }

        $this->email_mention_mode = $mode;
    }


    /**
     * @param  string $type
     * @return mixed
     */
    public function getFilterNotifyPrefs($type)
    {
        return $this->filter_notify_prefs[$type];
    }


    /**
     * @param string $type
     * @param array  $values
     */
    public function setFilterNotifyPrefs($type, array $values)
    {
        foreach ($values as $k => $v) {
            if (!isset($this->filter_notify_prefs[$type][$k])) {
                continue;
            }

            $this->filter_notify_prefs[$type][$k] = $v;
        }
    }


    /**
     * @param  string $type
     * @param  string $app_name
     * @return mixed
     */
    public function getAppSubs($type, $app_name = null)
    {
        return $this->app_subs[$app_name][$type];
    }


    /**
     * @param  string                    $type
     * @param  string                    $app_name
     * @param  array                     $values
     * @throws \InvalidArgumentException
     */
    public function setAppSubs($type, $app_name, $values)
    {
        foreach ($values as $k => $v) {
            if (!isset($this->app_subs[$app_name][$type][$k])) {
                continue;
            }
            $this->app_subs[$app_name][$type][$k] = $v;
        }
    }


    ####################################################################################################################
    # These methods return valid types for each category of notifications
    ####################################################################################################################

    /**
     * @param  TicketFilter              $filter
     * @param  string                    $type
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getFilterNotifyTypes(TicketFilter $filter, $type)
    {
        if ($type != self::TYPE_EMAIL && $type != self::TYPE_ALERT) {
            throw new \InvalidArgumentException("\$type must be `email` or `alert` (got `$type`)`");
        }

        // Built-in filters
        if ($filter->sys_name) {
            if ($filter->sys_name == 'all') {
                // All filter has no concept 'new/leave'
                $notify_types = array(
                    'created',
                    'user_activity',
                    'agent_activity',
                    'agent_note',
                    'property_change'
                );
            } else {
                $notify_types = array(
                    'created',
                    'new',
                    'leave',
                    'user_activity',
                    'agent_activity',
                    'agent_note',
                    'property_change'
                );
            }

        // Custom filters
        } else {
            $notify_types = array(
                'created',
                'new',
                'user_activity',
                'agent_activity',
                'agent_note',
                'property_change'
            );
        }

        return $notify_types;
    }


    /**
     * @param  string                    $type
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getFilterNotifyPrefNames($type)
    {
        return array(
            'override_all',
            'override_forward'
        );
    }


    /**
     * @param  string $type
     * @return array
     */
    public function getChatNotifyTypes($type)
    {
        if ($type == self::TYPE_EMAIL) {
            return array(
                'chat_message'
            );
        } else {
            return array();
        }
    }


    /**
     * @param  string $type
     * @return array
     */
    public function getTaskNotifyTypes($type)
    {
        return array(
            'task_assign_self',
            'task_assign_team',
            'task_complete',
            'task_due',
        );
    }


    /**
     * @param  string $type
     * @return array
     */
    public function getTwitterNotifyTypes($type)
    {
        return array(
            'tweet_assign_self',
            'tweet_assign_team',
            'tweet_reply',
            'tweet_new_dm',
            'tweet_new_reply',
            'tweet_new_mention',
            'tweet_new_retweet',
        );
    }


    /**
     * @param  string $type
     * @return array
     */
    public function getFeedbackNotifyTypes($type)
    {
        return array(
            'new_feedback',
            'new_feedback_validate',
        );
    }


    /**
     * @param  string $type
     * @return array
     */
    public function getPublishNotifyTypes($type)
    {
        return array(
            'new_comment',
            'new_comment_validate',
        );
    }


    /**
     * @param  string $type
     * @return array
     */
    public function getCrmNotifyTypes($type)
    {
        return array(
            'new_user',
            'new_user_validate',
        );
    }


    /**
     * @param  string $type
     * @return array
     */
    public function getAccountNotifyTypes($type)
    {
        if ($type == self::TYPE_EMAIL) {
            return array(
                'login_attempt',
                'login_attempt_fail',
            );
        } else {
            return array();
        }
    }
}
