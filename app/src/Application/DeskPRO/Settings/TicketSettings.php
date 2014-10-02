<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 */

namespace Application\DeskPRO\Settings;

use Orb\Util\Arrays;

class TicketSettings
{
	/**
	 * @var Settings
	 */
	private $settings;

	public $satisfaction_enabled           = false;
	public $satisfaction_agentread         = false;

	public $kbsuggest_web_enabled          = false;

	public $timelog_enabled                = false;
	public $timelog_autostart              = false;

	public $billinglog_enabled             = false;
	public $billinglog_currency            = false;

	public $lock_auto_enabled              = false;
	public $lock_autorelease_enabled       = false;
	public $lock_timeout                   = false;

	public $ref_enabled                    = false;
	public $ref_custom_enabled             = false;
	public $ref_custom_pattern             = '';
	public $ref_custom_pattern_digits      = 0;

	public $add_agent_ccs                  = false;
	public $gateway_max_email              = 0;

	public $from_email_headers;

	public $working_hours = null;

	public $agent_defaults = array(
		'newticket_status'      => 'awaiting_user',
		'newticket_agent'       => 'assign',
		'newticket_team'        => null,
		'newticket_user_notify' => true,

		'reply_status'                      => 'awaiting_agent',
		'reply_agent_unassigned'            => 'assign',
		'reply_agent_assigned'              => null,
		'reply_team_unassigned'             => null,
		'reply_team_assigned'               => null,
		'reply_user_notify'                 => true,
		'reply_reassign_auto_change_status' => true,
		'reply_resolve_auto_close_tab'      => true,

		'view_reverse_order' => true
	);


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
		$this->satisfaction_enabled       = (bool)$this->settings->get('core.tickets.enable_feedback');
		$this->satisfaction_agentread     = (bool)$this->settings->get('core.tickets.feedback_agents_read');

		$this->kbsuggest_web_enabled      = (bool)$this->settings->get('core.show_ticket_suggestions');

		$this->timelog_enabled            = (bool)$this->settings->get('core_tickets.enable_timelog');
		$this->timelog_autostart          = (bool)$this->settings->get('core_tickets.billing_auto_timer');

		$this->billinglog_enabled         = (bool)$this->settings->get('core_tickets.enable_billing');
		$this->billinglog_currency        = $this->settings->get('core_tickets.billing_currency');

		$this->lock_auto_enabled          = (bool)$this->settings->get('core_tickets.lock_on_view');
		$this->lock_autorelease_enabled   = (bool)$this->settings->get('core_tickets.unlock_on_close');
		$this->lock_timeout               = (int)$this->settings->get('core_tickets.lock_lifetime');

		$this->ref_enabled                = (bool)$this->settings->get('core.tickets.use_ref');

		if ($this->ref_enabled && $this->settings->get('core.ref_pattern')) {
			$this->ref_custom_enabled        = true;
			$this->ref_custom_pattern        = $this->settings->get('core.ref_pattern');
			$this->ref_custom_pattern_digits = (int)$this->settings->get('core.ref_append_counter');
		}

		$this->agent_defaults['newticket_status']                     = $this->settings->get('core_tickets.new_status');
		$this->agent_defaults['newticket_agent']                      = $this->settings->get('core_tickets.new_assign') ?: null;
		$this->agent_defaults['newticket_team']                       = $this->settings->get('core_tickets.new_assignteam') ?: null;
		$this->agent_defaults['newticket_user_notify']                = (bool)$this->settings->get('core_tickets.new_default_send_user_notify');
		$this->agent_defaults['reply_status']                         = $this->settings->get('core_tickets.reply_status');
		$this->agent_defaults['reply_agent_unassigned']               = $this->settings->get('core_tickets.reply_assign_unassigned') ?: null;
		$this->agent_defaults['reply_agent_assigned']                 = $this->settings->get('core_tickets.reply_assign_assigned') ?: null;
		$this->agent_defaults['reply_team_unassigned']                = $this->settings->get('core_tickets.reply_assignteam_unassigned') ?: null;
		$this->agent_defaults['reply_team_assigned']                  = $this->settings->get('core_tickets.reply_assignteam_assigned') ?: null;
		$this->agent_defaults['reply_user_notify']                    = (bool)$this->settings->get('core_tickets.default_send_user_notify');
		$this->agent_defaults['reply_reassign_auto_change_status']    = (bool)$this->settings->get('core_tickets.reassign_auto_change_status');
		$this->agent_defaults['reply_resolve_auto_close_tab']         = (bool)$this->settings->get('core_tickets.resolve_auto_close_tab');
		$this->agent_defaults['view_reverse_order']                   = (bool)$this->settings->get('core_tickets.default_ticket_reverse_order');

		$this->add_agent_ccs     = (bool)$this->settings->get('core_tickets.add_agent_ccs');
		$this->gateway_max_email = (int)$this->settings->get('core.gateway_max_email');

		$wh = $this->settings->get('core_tickets.work_hours');
		if ($wh) {
			$wh = @unserialize($wh);
		}
		if ($wh) {
			$this->working_hours = $wh;
		}

		$this->from_email_headers = explode(',', $this->settings->get('core_email.from_email_headers'));
		$this->from_email_headers = Arrays::func($this->from_email_headers, 'trim');
		$this->from_email_headers = Arrays::removeFalsey($this->from_email_headers);
		$this->from_email_headers = Arrays::func($this->from_email_headers, 'strtolower');

		if (!$this->from_email_headers) {
			$this->from_email_headers = array('from', 'reply-to', 'x-original-from');
		}
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		$export_settings = array();

		foreach (array(
			'satisfaction_enabled',
			'satisfaction_agentread',
			'kbsuggest_web_enabled',
			'timelog_enabled',
			'timelog_autostart',
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
			'working_hours',
			'from_email_headers',
		) as $s) {
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
					$this->from_email_headers = array('from', 'reply-to', 'x-original-from');
				}
			} else {
				if (property_exists($this, $s)) {
					$this->$s = $val;
				}
			}
		}
	}


	/**
	 * Persists settings
	 */
	public function saveSettings()
	{
		$this->settings->setSetting('core.tickets.enable_feedback',      (int)$this->satisfaction_enabled);
		$this->settings->setSetting('core.tickets.feedback_agents_read', (int)$this->satisfaction_agentread);
		$this->settings->setSetting('core.show_ticket_suggestions',      (int)$this->kbsuggest_web_enabled);

		$this->settings->setSetting('core_tickets.enable_timelog',       (int)$this->timelog_enabled);

		if ($this->timelog_enabled) {
			$this->settings->setSetting('core_tickets.billing_auto_timer',   (int)$this->timelog_autostart);
		} else {
			$this->settings->setSetting('core_tickets.billing_auto_timer',   0);
		}

		$this->settings->setSetting('core_tickets.enable_billing',       (int)$this->billinglog_enabled);
		$this->settings->setSetting('core_tickets.billing_currency',     $this->billinglog_currency);

		$this->settings->setSetting('core_tickets.lock_on_view',         (int)$this->lock_auto_enabled);
		$this->settings->setSetting('core_tickets.unlock_on_close',      (int)$this->lock_autorelease_enabled);
		$this->settings->setSetting('core_tickets.lock_lifetime',        (int)$this->lock_timeout);

		$this->settings->setSetting('core.tickets.use_ref',              (int)$this->ref_enabled);

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

		$wh = $this->working_hours;
		if ($wh) {
			$this->settings->setSetting('core_tickets.work_hours', serialize($wh));
		} else {
			$this->settings->setSetting('core_tickets.work_hours', null);
		}

		$this->settings->setSetting('core_email.from_email_headers', implode(',', $this->from_email_headers));
	}
}