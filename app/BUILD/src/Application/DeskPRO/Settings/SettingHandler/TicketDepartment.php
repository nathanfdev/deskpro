<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings\SettingHandler;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Settings\Settings as SettingsHandler;

class TicketDepartment
{
    /**
     * @var \Application\DeskPRO\Settings\Settings
     */
    protected $settings;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    public function __construct(SettingsHandler $settings, Connection $db)
    {
        $this->settings = $settings;
        $this->db       = $db;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $settings = [
            'core.default_ticket_dep'         => $this->settings->get('core.default_ticket_dep'),
            'core.phrase_department_singular' => $this->settings->get('core.phrase_department_singular'),
            'core.phrase_department_plural'   => $this->settings->get('core.phrase_department_plural'),
        ];

        return $settings;
    }

    /**
     * @param array $set_settings
     *
     * @throws \Exception
     */
    public function setSettings(array $set_settings)
    {
        if (isset($set_settings['core.default_ticket_dep']) && $set_settings['core.default_ticket_dep'] != $this->settings->get('core.default_ticket_dep')) {
            $this->settings->setSetting('core.default_ticket_dep', $set_settings['core.default_ticket_dep']);
        }

        $change_phrase = [];
        if (isset($set_settings['core.phrase_department_singular']) && $set_settings['core.phrase_department_singular'] != $this->settings->get('core.phrase_department_singular')) {
            $change_phrase['singular'] = $set_settings['core.phrase_department_singular'];
        }
        if (isset($set_settings['core.phrase_department_plural']) && $set_settings['core.phrase_department_plural'] != $this->settings->get('core.phrase_department_plural')) {
            $change_phrase['plural'] = $set_settings['core.phrase_department_singular'];
        }

        if ($change_phrase) {
            if (!isset($change_phrase['singular'])) {
                $change_phrase['singular'] = $this->settings->get('core.phrase_department_singular');
            }
            if (!isset($change_phrase['plural'])) {
                $change_phrase['plural'] = $this->settings->get('core.phrase_department_plural');
            }

            $this->settings->setSetting('core.phrase_department_singular', $change_phrase['singular']);
            $this->settings->setSetting('core.phrase_department_plural', $change_phrase['plural']);
        }
    }
}
