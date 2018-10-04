<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings;

use Orb\Util\Arrays;

class TicketSettings
{
    /**
     * @var Settings
     */
    private $settings;

    /** @var bool */
    public $satisfaction_enabled = false;
    /** @var bool */
    public $satisfaction_agentread = false;

    /** @var bool */
    public $kbsuggest_web_enabled = false;

    /** @var bool */
    public $timelog_enabled = false;
    /** @var bool */
    public $timelog_autostart = false;
    /** @var bool */
    public $billing_on_reply = false;

    /** @var bool */
    public $billinglog_enabled = false;
    /** @var bool */
    public $billinglog_currency = false;

    /** @var bool */
    public $lock_auto_enabled = false;
    /** @var bool */
    public $lock_autorelease_enabled = false;
    /** @var bool */
    public $lock_timeout = false;

    /** @var bool */
    public $ref_enabled = false;
    /** @var bool */
    public $ref_custom_enabled = false;
    /** @var string */
    public $ref_custom_pattern = '';
    /** @var int */
    public $ref_custom_pattern_digits = 0;

    /** @var bool */
    public $add_agent_ccs = false;
    /** @var int */
    public $gateway_max_email = 0;
    /** @var int */
    public $email_cc_max_count = 20;

    /** @var array */
    public $from_email_headers;

    /** @var array|null */
    public $working_hours = null;

    /** @var bool|false */
    public $email_reply_as_note = false;

    /** @var bool */
    public $web_require_validation = false;

    /** @var bool */
    public $email_require_validation = false;

    /** @var bool */
    public $attachment_require_auth = false;

    public $agent_defaults = [
        'newticket_status'        => 'awaiting_user',
        'newticket_agent'         => 'assign',
        'newticket_team'          => null,
        'newticket_user_notify'   => true,
        'newticket_enable_drafts' => true,

        'reply_status'                      => 'awaiting_agent',
        'reply_agent_unassigned'            => 'assign',
        'reply_agent_assigned'              => null,
        'reply_team_unassigned'             => null,
        'reply_team_assigned'               => null,
        'reply_user_notify'                 => true,
        'reply_reassign_auto_change_status' => true,
        'reply_resolve_auto_close_tab'      => true,

        'view_reverse_order' => true,
    ];

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->resetSettings();
    }

    /**
     * Resets settings based on stored values.
     */
    public function resetSettings()
    {
        $this->satisfaction_enabled   = (bool) $this->settings->get('core_tickets.enable_feedback');
        $this->satisfaction_agentread = (bool) $this->settings->get('core_tickets.feedback_agents_read');

        $this->web_require_validation   = (bool) $this->settings->get('core_tickets.web_require_validation');
        $this->email_require_validation = (bool) $this->settings->get('core_tickets.email_require_validation');

        $this->attachment_require_auth = (bool) $this->settings->get('core_tickets.attachment_require_auth');

        $this->kbsuggest_web_enabled = (bool) $this->settings->get('core.show_ticket_suggestions');

        $this->timelog_enabled   = (bool) $this->settings->get('core_tickets.enable_timelog');
        $this->timelog_autostart = (bool) $this->settings->get('core_tickets.billing_auto_timer');
        $this->billing_on_reply  = (bool) $this->settings->get('core_tickets.billing_on_reply');

        $this->billinglog_enabled  = (bool) $this->settings->get('core_tickets.enable_billing');
        $this->billinglog_currency = $this->settings->get('core_tickets.billing_currency');

        $this->lock_auto_enabled        = (bool) $this->settings->get('core_tickets.lock_on_view');
        $this->lock_autorelease_enabled = (bool) $this->settings->get('core_tickets.unlock_on_close');
        $this->lock_timeout             = (int) $this->settings->get('core_tickets.lock_lifetime');

        $this->ref_enabled = (bool) $this->settings->get('core_tickets.use_ref');

        if ($this->ref_enabled && $this->settings->get('core.ref_pattern')) {
            $this->ref_custom_enabled        = true;
            $this->ref_custom_pattern        = $this->settings->get('core.ref_pattern');
            $this->ref_custom_pattern_digits = (int) $this->settings->get('core.ref_append_counter');
        }

        $this->agent_defaults['newticket_status']                  = $this->settings->get('core_tickets.new_status');
        $this->agent_defaults['newticket_agent']                   = $this->settings->get('core_tickets.new_assign') ?: null;
        $this->agent_defaults['newticket_team']                    = $this->settings->get('core_tickets.new_assignteam') ?: null;
        $this->agent_defaults['newticket_user_notify']             = (bool) $this->settings->get('core_tickets.new_default_send_user_notify');
        $this->agent_defaults['newticket_enable_drafts']           = (bool) $this->settings->get('core_tickets.newticket_enable_drafts');
        $this->agent_defaults['reply_status']                      = $this->settings->get('core_tickets.reply_status');
        $this->agent_defaults['reply_agent_unassigned']            = $this->settings->get('core_tickets.reply_assign_unassigned') ?: null;
        $this->agent_defaults['reply_agent_assigned']              = $this->settings->get('core_tickets.reply_assign_assigned') ?: null;
        $this->agent_defaults['reply_team_unassigned']             = $this->settings->get('core_tickets.reply_assignteam_unassigned') ?: null;
        $this->agent_defaults['reply_team_assigned']               = $this->settings->get('core_tickets.reply_assignteam_assigned') ?: null;
        $this->agent_defaults['reply_user_notify']                 = (bool) $this->settings->get('core_tickets.default_send_user_notify');
        $this->agent_defaults['reply_reassign_auto_change_status'] = (bool) $this->settings->get('core_tickets.reassign_auto_change_status');
        $this->agent_defaults['reply_resolve_auto_close_tab']      = (bool) $this->settings->get('core_tickets.resolve_auto_close_tab');
        $this->agent_defaults['view_reverse_order']                = (bool) $this->settings->get('core_tickets.default_ticket_reverse_order');

        $this->add_agent_ccs      = (bool) $this->settings->get('core_tickets.add_agent_ccs');
        $this->gateway_max_email  = (int) $this->settings->get('core.gateway_max_email');
        $this->email_cc_max_count = (int) $this->settings->get('core_tickets.email_cc_max_count');

        $wh = $this->settings->get('core_tickets.work_hours');
        if ($wh) {
            $wh = @unserialize($wh);
        }
        if ($wh) {
            $this->working_hours = $wh;
        } else {
            $this->working_hours = [
                'timezone'   => 'UTC',
                'start_hour' => 9,
                'start_min'  => 0,
                'end_hour'   => 17,
                'end_min'    => 0,
                'holidays'   => [],
                'work_days'  => [],
            ];
        }

        if (!@$this->working_hours['holidays']) {
            $this->working_hours['holidays'] = [];
        }
        if (!@$this->working_hours['work_days']) {
            $this->working_hours['work_days'] = [];
        }

        $this->from_email_headers = explode(',', $this->settings->get('core_email.from_email_headers'));
        $this->from_email_headers = Arrays::func($this->from_email_headers, 'trim');
        $this->from_email_headers = Arrays::removeFalsey($this->from_email_headers);
        $this->from_email_headers = Arrays::func($this->from_email_headers, 'strtolower');

        if (!$this->from_email_headers) {
            $this->from_email_headers = ['from', 'reply-to', 'x-original-from'];
        }

        $this->email_reply_as_note = (bool) $this->settings->get('core_tickets.email_reply_as_note');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $export_settings = [];

        foreach ([
            'satisfaction_enabled',
            'satisfaction_agentread',
            'web_require_validation',
            'email_require_validation',
            'attachment_require_auth',
            'kbsuggest_web_enabled',
            'timelog_enabled',
            'timelog_autostart',
            'billing_on_reply',
            'billinglog_enabled',
            'billinglog_currency',
            'lock_auto_enabled',
            'lock_autorelease_enabled',
            'lock_timeout',
            'ref_enabled',
            'ref_custom_enabled',
            'ref_custom_pattern',
            'ref_custom_pattern_digits',
            'agent_defaults',
            'add_agent_ccs',
            'gateway_max_email',
            'email_cc_max_count',
            'working_hours',
            'from_email_headers',
            'email_reply_as_note',
        ] as $s) {
            $export_settings[$s] = $this->$s;
        }

        return $export_settings;
    }

    /**
     * @param array $set_settings
     */
    public function setArray(array $set_settings)
    {
        foreach ($set_settings as $s => $val) {
            if ($s == 'from_email_headers') {
                $this->from_email_headers = $val;
                $this->from_email_headers = Arrays::func($this->from_email_headers, 'trim');
                $this->from_email_headers = Arrays::removeFalsey($this->from_email_headers);
                $this->from_email_headers = Arrays::func($this->from_email_headers, 'strtolower');
                if (!$this->from_email_headers) {
                    $this->from_email_headers = ['from', 'reply-to', 'x-original-from'];
                }
            } else {
                if (property_exists($this, $s)) {
                    $this->$s = $val;
                }
            }
        }
    }

    /**
     * Persists settings.
     */
    public function saveSettings()
    {
        $this->settings->setSetting('core_tickets.enable_feedback',      (int) $this->satisfaction_enabled);
        $this->settings->setSetting('core_tickets.feedback_agents_read', (int) $this->satisfaction_agentread);
        $this->settings->setSetting('core.show_ticket_suggestions',      (int) $this->kbsuggest_web_enabled);

        $this->settings->setSetting('core_tickets.web_require_validation', (int) $this->web_require_validation);
        $this->settings->setSetting('core_tickets.email_require_validation', (int) $this->email_require_validation);

        $this->settings->setSetting('core_tickets.attachment_require_auth', (int) $this->attachment_require_auth);

        $this->settings->setSetting('core_tickets.enable_timelog',       (int) $this->timelog_enabled);

        if ($this->timelog_enabled) {
            $this->settings->setSetting('core_tickets.billing_auto_timer',   (int) $this->timelog_autostart);
            $this->settings->setSetting('core_tickets.billing_on_reply',   (int) $this->billing_on_reply);
        } else {
            $this->settings->setSetting('core_tickets.billing_auto_timer',   0);
            $this->settings->setSetting('core_tickets.billing_on_reply',   0);
        }

        $this->settings->setSetting('core_tickets.enable_billing',       (int) $this->billinglog_enabled);
        $this->settings->setSetting('core_tickets.billing_currency',     $this->billinglog_currency);

        $this->settings->setSetting('core_tickets.lock_on_view',         (int) $this->lock_auto_enabled);
        $this->settings->setSetting('core_tickets.unlock_on_close',      (int) $this->lock_autorelease_enabled);
        $this->settings->setSetting('core_tickets.lock_lifetime',        (int) $this->lock_timeout);

        $this->settings->setSetting('core_tickets.use_ref',              (int) $this->ref_enabled);

        if ($this->ref_enabled) {
            if ($this->ref_custom_enabled) {
                $this->settings->setSetting('core.ref_pattern', $this->ref_custom_pattern);

                $this->settings->setSetting('core.ref_append_counter', $this->ref_custom_pattern_digits);
            } else {
                $this->settings->setSetting('core.ref_pattern', '');
                $this->settings->setSetting('core.ref_append_counter', 0);
            }
        } else {
            $this->settings->setSetting('core.ref_pattern', '');
            $this->settings->setSetting('core.ref_append_counter', '0');
        }

        $this->settings->setSetting('core_tickets.new_status',                    $this->agent_defaults['newticket_status']);
        $this->settings->setSetting('core_tickets.new_assign',                    $this->agent_defaults['newticket_agent']);
        $this->settings->setSetting('core_tickets.new_assignteam',                $this->agent_defaults['newticket_team']);
        $this->settings->setSetting('core_tickets.new_default_send_user_notify',  $this->agent_defaults['newticket_user_notify'] ? 1 : 0);
        $this->settings->setSetting('core_tickets.newticket_enable_drafts',       $this->agent_defaults['newticket_enable_drafts'] ? 1 : 0);
        $this->settings->setSetting('core_tickets.reply_status',                  $this->agent_defaults['reply_status']);
        $this->settings->setSetting('core_tickets.reply_assign_unassigned',       $this->agent_defaults['reply_agent_unassigned']);
        $this->settings->setSetting('core_tickets.reply_assign_assigned',         $this->agent_defaults['reply_agent_assigned']);
        $this->settings->setSetting('core_tickets.reply_assignteam_unassigned',   $this->agent_defaults['reply_team_unassigned']);
        $this->settings->setSetting('core_tickets.reply_assignteam_assigned',     $this->agent_defaults['reply_team_assigned']);
        $this->settings->setSetting('core_tickets.default_send_user_notify',      $this->agent_defaults['reply_user_notify']);
        $this->settings->setSetting('core_tickets.reassign_auto_change_status',   $this->agent_defaults['reply_reassign_auto_change_status']);
        $this->settings->setSetting('core_tickets.resolve_auto_close_tab',        $this->agent_defaults['reply_resolve_auto_close_tab']);
        $this->settings->setSetting('core_tickets.default_ticket_reverse_order',  $this->agent_defaults['view_reverse_order']);

        $this->settings->setSetting('core_tickets.add_agent_ccs', $this->add_agent_ccs);
        $this->settings->setSetting('core.gateway_max_email', $this->gateway_max_email ?: null);
        $this->settings->setSetting('core_tickets.email_cc_max_count', $this->email_cc_max_count !== null ? (int) $this->email_cc_max_count : null);

        $wh = $this->working_hours;
        if ($wh) {
            $this->settings->setSetting('core_tickets.work_hours', serialize($wh));
        } else {
            $this->settings->setSetting('core_tickets.work_hours', null);
        }

        $this->settings->setSetting('core_email.from_email_headers', implode(',', $this->from_email_headers));
        $this->settings->setSetting('core_tickets.email_reply_as_note', $this->email_reply_as_note);
    }
}
