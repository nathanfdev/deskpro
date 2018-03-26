<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings;

use Orb\Util\Numbers;

class PasswordSettings
{
    /**
     * @var \Application\DeskPRO\Settings\Settings
     */
    private $settings;

    /**
     * @var PasswordPolicy
     */
    private $user_policy;

    /**
     * @var PasswordPolicy
     */
    private $agent_policy;

    /** @var int */
    public $sessions_lifetime = 3600;
    /** @var bool */
    public $session_keepalive_require_page = false;
    /** @var bool */
    public $ip_security_enabled = false;
    /** @var string */
    public $ip_security_mode = 'admins';
    /** @var int */
    public $ip_security_whitelist_lifetime = 1814400;
    /** @var bool */
    public $disable_notifications;
    /** @var bool */
    public $enable_agent_rememberme;
    /** @var bool */
    public $enable_user_rememberme;
    /** @var bool */
    public $agent_enable_kb_shortcuts;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;

        $this->user_policy  = new PasswordPolicy();
        $this->agent_policy = new PasswordPolicy();

        $this->resetSettings();
    }

    /**
     * @return PasswordPolicy
     */
    public function getUserPolicy()
    {
        return $this->user_policy;
    }

    /**
     * @return PasswordPolicy
     */
    public function getAgentPolicy()
    {
        return $this->agent_policy;
    }

    /**
     * Resets settings based on stored values.
     */
    public function resetSettings()
    {
        foreach (['user', 'agent'] as $type) {
            $type_obj = $this->{$type.'_policy'};

            foreach ([
                'min_length',
                'max_age',
                'forbid_reuse',
                'require_num_uppercase',
                'require_num_lowercase',
                'require_num_number',
                'require_num_symbol',
            ] as $name) {
                $type_obj->$name = $this->settings->get("$type.password_policy.$name");
            }

            $type_obj->verify();
        }

        $this->sessions_lifetime              = (int) $this->settings->get('core.sessions_lifetime');
        $this->session_keepalive_require_page = (bool) $this->settings->get('core.session_keepalive_require_page');
        $this->ip_security_enabled            = (bool) $this->settings->get('agent.ip_security.enabled');
        $this->ip_security_mode               = $this->settings->get('agent.ip_security.mode');
        $this->ip_security_whitelist_lifetime = (int) $this->settings->get('agent.ip_security.whitelist_lifetime');
        $this->disable_notifications          = (bool) $this->settings->get('agent.disable_notifications');
        $this->enable_agent_rememberme        = (bool) $this->settings->get('core.enable_agent_rememberme');
        $this->enable_user_rememberme         = (bool) $this->settings->get('core.enable_user_rememberme');
        $this->agent_enable_kb_shortcuts      = (bool) $this->settings->get('core.agent_enable_kb_shortcuts');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'user'                           => $this->user_policy->toArray(),
            'agent'                          => $this->agent_policy->toArray(),
            'sessions_lifetime'              => $this->sessions_lifetime,
            'session_keepalive_require_page' => $this->session_keepalive_require_page,
            'ip_security_enabled'            => $this->ip_security_enabled,
            'ip_security_mode'               => $this->ip_security_mode,
            'ip_security_whitelist_lifetime' => $this->ip_security_whitelist_lifetime,
            'disable_notifications'          => $this->disable_notifications,
            'enable_agent_rememberme'        => $this->enable_agent_rememberme,
            'enable_user_rememberme'         => $this->enable_user_rememberme,
            'agent_enable_kb_shortcuts'      => $this->agent_enable_kb_shortcuts,
        ];
    }

    /**
     * @param array $set_settings
     */
    public function setArray(array $set_settings)
    {
        $this->user_policy->fromArray($set_settings['user']);
        $this->agent_policy->fromArray($set_settings['agent']);
        $this->sessions_lifetime              = $set_settings['sessions_lifetime'];
        $this->session_keepalive_require_page = $set_settings['session_keepalive_require_page'];

        $this->ip_security_enabled            = !empty($set_settings['ip_security_enabled']) && $set_settings['ip_security_enabled'];
        $this->ip_security_mode               = $set_settings['ip_security_mode'] ?: 'admins';
        $this->ip_security_whitelist_lifetime = ((int) $set_settings['ip_security_whitelist_lifetime']) ?: 1814400;
        $this->disable_notifications          = (bool) $set_settings['disable_notifications'];
        $this->enable_agent_rememberme        = (bool) $set_settings['enable_agent_rememberme'];
        $this->enable_user_rememberme         = (bool) $set_settings['enable_user_rememberme'];
        $this->agent_enable_kb_shortcuts      = (bool) $set_settings['agent_enable_kb_shortcuts'];
    }

    /**
     * Persists settings.
     */
    public function saveSettings()
    {
        foreach (['user', 'agent'] as $type) {
            $type_obj = $this->{$type.'_policy'};

            foreach ($type_obj->toArray() as $k => $v) {
                if (is_bool($v)) {
                    $v = $v ? 1 : 0;
                }
                $this->settings->setSetting("$type.password_policy.$k", $v);
            }
        }

        $this->settings->setSetting('core.sessions_lifetime', Numbers::bound($this->sessions_lifetime, 300, 86400));

        if ($this->session_keepalive_require_page) {
            $this->settings->setSetting('core.session_keepalive_require_page', 1);
        } else {
            $this->settings->setSetting('core.session_keepalive_require_page', null);
        }

        $this->settings->setSetting('agent.ip_security.enabled',            $this->ip_security_enabled ? 1 : 0);
        $this->settings->setSetting('agent.ip_security.mode',               $this->ip_security_mode);
        $this->settings->setSetting('agent.ip_security.whitelist_lifetime', $this->ip_security_whitelist_lifetime);
        $this->settings->setSetting('agent.disable_notifications',          (bool) $this->disable_notifications);
        $this->settings->setSetting('core.enable_agent_rememberme',         (bool) $this->enable_agent_rememberme);
        $this->settings->setSetting('core.enable_user_rememberme',          (bool) $this->enable_user_rememberme);
        $this->settings->setSetting('core.agent_enable_kb_shortcuts', (bool) $this->agent_enable_kb_shortcuts);
    }
}
