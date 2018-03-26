<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings;

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Orb\Util\OptionsArray;

class TicketFwdSettings
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\EmailAccountManager
     */
    private $email_accounts;

    /** @var int */
    public $use_account;
    /** @var bool */
    public $use_agent_address;
    /** @var bool */
    public $process_agent_fwd;
    /** @var string */
    public $agent_fwd_subject_regex;
    /** @var bool */
    public $email_fwd_reply_as_note;

    /**
     * @param Settings            $settings
     * @param EmailAccountManager $email_accounts
     */
    public function __construct(Settings $settings, EmailAccountManager $email_accounts)
    {
        $this->settings       = $settings;
        $this->email_accounts = $email_accounts;

        $this->resetSettings();
    }

    /**
     * Resets settings based on stored values.
     */
    public function resetSettings()
    {
        $this->use_account             = (int) $this->settings->get('core_tickets.fwd_use_account');
        $this->use_agent_address       = (bool) $this->settings->get('core_tickets.fwd_use_agent_address');
        $this->process_agent_fwd       = (bool) $this->settings->get('core_tickets.process_agent_fwd');
        $this->email_fwd_reply_as_note = (bool) $this->settings->get('core_tickets.email_fwd_reply_as_note');
        $this->agent_fwd_subject_regex = $this->settings->get('core_tickets.agent_fwd_subject_regex') ?: '';

        if (!$this->use_account || !$this->email_accounts->hasAcccount($this->use_account)) {
            $this->use_account = null;
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $export_settings = [];

        foreach ([
            'use_account',
            'use_agent_address',
            'process_agent_fwd',
            'agent_fwd_subject_regex',
            'email_fwd_reply_as_note',
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
        $set_settings = new OptionsArray($set_settings);

        $this->use_account             = (int) $set_settings->get('use_account');
        $this->use_agent_address       = (bool) $set_settings->get('use_agent_address');
        $this->process_agent_fwd       = (bool) $set_settings->get('process_agent_fwd');
        $this->email_fwd_reply_as_note = (bool) $set_settings->get('email_fwd_reply_as_note');
        $this->agent_fwd_subject_regex = $set_settings->get('agent_fwd_subject_regex');

        if (!$this->use_account || !$this->email_accounts->hasAcccount($this->use_account)) {
            $this->use_account = null;
        }
    }

    /**
     * Persists settings.
     */
    public function saveSettings()
    {
        $this->settings->setSetting('core_tickets.fwd_use_account', $this->use_account);
        $this->settings->setSetting('core_tickets.fwd_use_agent_address', $this->use_agent_address);
        $this->settings->setSetting('core_tickets.process_agent_fwd', $this->process_agent_fwd);
        $this->settings->setSetting('core_tickets.email_fwd_reply_as_note', $this->email_fwd_reply_as_note);
        $this->settings->setSetting('core_tickets.agent_fwd_subject_regex', $this->agent_fwd_subject_regex ?: null);
    }
}
