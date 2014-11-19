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
| ~ Thanks, Everyone at Team DeskPRO                                           |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Settings
 */

namespace Application\DeskPRO\Settings;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;


/**
 * DEPRECEATED way of getting settings
 *
 * This class fethces settings
 *
 * @deprecated get the "settings_resolver" system service and fetch the SettingsBag you want from it instead.
 *             this exists only for BC.
 */
class Settings implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var \Application\DeskPRO\NewSettings\SettingsBag
     */
    private $settings;

    /**
     * @var \Application\DeskPRO\NewSettings\SettingsBag
     */
    private $default_settings;

    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver $new_settings_resolver
     */
    private $new_settings_resolver;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;


    /**
     * DEPRECEATED way of getting settings
     *
     * @deprecated get the "settings_resolver" system service and fetch the SettingsBag you want from it instead.
     *             this exists only for BC.
     *
     * @param string     $default_settings_file
     * @param Connection $db
     */
    public function __construct($default_settings_file, Connection $db = null)
    {
        $this->new_settings_resolver = App::getSystemService('settings_resolver');
        $this->settings         = $this->new_settings_resolver->getGlobalSettings();
        $this->default_settings = $this->new_settings_resolver->getDefaultSettings();
        $this->db = $db;
    }


    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function reloadSettings()
    {
        $this->settings         = $this->new_settings_resolver->getGlobalSettings(true);
        $this->default_settings = $this->new_settings_resolver->getDefaultSettings(true);
    }


    /**
     * Get the value of a setting
     *
     * @param $name
     * @param  null       $default
     * @return mixed|null
     */
    public function get($name, $default = null)
    {
        return $this->settings->get($name, $default);
    }


    public function getAll()
    {
        return $this->settings->toArray();
    }


    /**
     * This loads the default for a value as defined in the setting file
     *
     * @param  string                       $name
     * @return null
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function getDefault($name)
    {
        return $this->default_settings->get($name);
    }


    /**
     * Get the default values for an entire group
     *
     * @param  string                       $group
     * @param  bool                         $short True to strip off the group name, false to include the group name in the key
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function getDefaultGroup($group, $short = true)
    {
        $this->default_settings->getGroup($group, $short);
    }


    /**
     * Get all settings in a group
     *
     * @param  string                       $group
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function getGroup($group)
    {
        return $this->settings->getGroup($group);
    }



    /**
     * Manually set the value for one or more settings. Note that these values are
     * temporary, they are NOT persisted. This is mainly useful for code overrides
     * or the like.
     *
     * @param  array                        $settings
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function setTemporarySettingValues(array $settings)
    {
        //
        // left for BC
        //
        $this->settings->setArray(array_merge($this->settings->toArray(), $settings));
    }


    /**
     * Persist a new value for a setting, and update this as well
     *
     * @param  string                       $setting
     * @param  string                       $value
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     * @deprecated don't do this going forard. instead change the source of the setting and use the "settings_resolver" system service
     */
    public function setSetting($setting, $value)
    {
        //
        // it is not an option in the new settings resolver to SET settings directly. This is left for BC.
        //
        $this->db->beginTransaction();
        try {

            if ($value !== null) {
                if ($value === true) $value = '1';
                else if ($value === false) $value = '0';

                $this->db->executeUpdate("
                    INSERT INTO settings
                        (name, value)
                    VALUES
                        (?, ?)
                    ON DUPLICATE KEY UPDATE
                        value = VALUES(value)
                ", array($setting, $value));
            } else {
                $this->db->delete('settings', array('name' => $setting));
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        $this->reloadSettings();
    }

    /**
     * @return \DateTimeZone
     */
    public function getDefaultTimezone()
    {
        return $this->new_settings_resolver->getGlobalSettings()->get('default_timezone');
    }

    public function offsetExists($offset)
    {
        return $this->settings->has($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('You cannot set settings');
    }

    public function offsetGet($offset)
    {
        return $this->settings->get($offset);
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('You cannot unset settings');
    }

    public function count()
    {
        return count($this->settings);
    }

    public function getIterator()
    {
        return $this->settings->getIterator();
    }
}
