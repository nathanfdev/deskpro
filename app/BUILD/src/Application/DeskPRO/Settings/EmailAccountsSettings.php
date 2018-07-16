<?php

namespace Application\DeskPRO\Settings;

/**
 * Class EmailAccountsSettings.
 */
class EmailAccountsSettings
{
    const PREFIX = 'core.emails';

    const DEFAULT_RATE_COUNT     = 15;
    const DEFAULT_RATE_TIME      = 600;
    const DEFAULT_RATE_LOCK_TIME = 900;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var array
     */
    protected $values = [
        'attach_agent_maxsize'     => 26214400,
        'attach_agent_must_exts'   => [],
        'attach_agent_not_exts'    => [],
        'attach_user_maxsize'      => 26214400,
        'attach_user_must_exts'    => [],
        'attach_user_not_exts'     => [],
        'sendemail_attach_maxsize' => 7340032,

        'rate_count'    => self::DEFAULT_RATE_COUNT,
        'rate_time'     => self::DEFAULT_RATE_TIME,
        'rate_locktime' => self::DEFAULT_RATE_LOCK_TIME,

        'download_hotlinked_images.enabled'       => true,
        'download_hotlinked_images.image_maxsize' => 10485760,
        'download_hotlinked_images.total_maxsize' => 26214400,
    ];

    /**
     * @var array
     */
    protected $otherValues = [
        'core_tickets.enable_dupe_checking'                 => true,
        'core_tickets.enable_email_preview'                 => true,
        'core_tickets.gateway_enable_subject_match'         => true,
        'core_tickets.enable_same_account_subject_matching' => false,
        'core_tickets.enable_exact_subject_matching'        => false,
        'core_tickets.reject_spf_level'                     => false,
        'core_tickets.reject_dkim_level'                    => false,
        'core_tickets.disable_attachments_list'             => false,
    ];

    /**
     * Constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->values as $k => $v) {
            if ($k == 'sendemail_attach_maxsize') {
                $storedValue = $this->settings->get('core.sendemail_attach_maxsize');
            } else {
                $storedValue = $this->settings->get(self::PREFIX.'.'.$k, $v);
            }

            if (in_array($k, ['download_hotlinked_images.enabled'])) {
                $data[$k] = $this->values[$k] = (bool) $storedValue;
            } else {
                if (is_int($v)) {
                    $storedValue = (int) $storedValue;
                } elseif (is_array($v)) {
                    $storedValue = $storedValue ? explode(',', $storedValue) : $v;
                }

                $data[$k] = $this->values[$k] = $storedValue ?: $v;
            }
        }

        foreach ($this->otherValues as $k => $v) {
            if (preg_match('|level$|', $k)) {
                $data[str_replace('.', '_', $k)] = $this->settings->get($k);
            } else {
                $data[str_replace('.', '_', $k)] = (bool) $this->settings->get($k);
            }
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function fromArray(array $data = [])
    {
        foreach ($data as $k => $v) {
            if (!array_key_exists($k, $this->values)) {
                continue;
            }

            $storeValue = $v;
            if (in_array($k, ['download_hotlinked_images.enabled'])) {
                $storeValue = $v = (bool) $v;
            } elseif (is_int($this->values[$k])) {
                $storeValue = $v = (int) $v;
            } elseif (is_array($this->values[$k])) {
                $v          = (array) $v;
                $storeValue = implode(',', $v);
            }
            $this->values[$k] = $v;

            if ($k == 'sendemail_attach_maxsize') {
                $this->settings->setSetting('core.sendemail_attach_maxsize', (int) $storeValue ?: null);
            } else {
                $this->settings->setSetting(self::PREFIX.'.'.$k, $storeValue);
            }
        }

        foreach ($data as $k => $v) {
            $k = str_replace('core_tickets_', 'core_tickets.', $k);
            if (!isset($this->otherValues[$k])) {
                continue;
            }

            if (!preg_match('|level$|', $k)) {
                $v = (int) ((bool) $v);
            }
            $this->settings->setSetting($k, $v);
            $this->otherValues[$k] = $v;
        }
    }
}
