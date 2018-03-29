<?php

/**
 * DeskPRO.
 *
 * @category Settings
 */

namespace Application\DeskPRO\Settings;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\AuditBundle\Document\AuditLogData;
use DeskPRO\Bundle\AuditBundle\Log\AuditLog;
use DeskPRO\Component\Util\TypeUtils;

/**
 * DEPRECEATED way of getting settings.
 *
 * This class fethces settings.
 *
 * @deprecated get the "settings_resolver" system service and fetch the SettingsBag you want from it instead.
 *             this exists only for BC
 */
class Settings implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var \Application\DeskPRO\NewSettings\SettingsBag
     */
    private $settings;

    /**
     * Plain database connection for raw queries.
     *
     * @var \Application\DeskPRO\NewSettings\SettingsBag
     */
    private $default_settings;

    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver
     */
    private $new_settings_resolver;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * DEPRECEATED way of getting settings.
     *
     * @deprecated get the "settings_resolver" system service and fetch the SettingsBag you want from it instead.
     *             this exists only for BC
     *
     * @param string     $default_settings_file
     * @param Connection $db
     */
    public function __construct($default_settings_file, Connection $db = null)
    {
        $this->new_settings_resolver = App::getSystemService('settings_resolver');
        $this->db                    = $db;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function reloadSettings()
    {
        $this->settings         = null;
        $this->default_settings = null;
    }

    /**
     * Get the value of a setting.
     *
     * @param $name
     * @param null $default
     *
     * @return mixed|null
     */
    public function get($name, $default = null)
    {
        if (!$name) {
            return $default;
        }

        return $this->getSettings()->get($name, $default);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->getSettings()->toArray();
    }

    /**
     * This loads the default for a value as defined in the setting file.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getDefault($name)
    {
        return $this->getDefaultSettings()->get($name);
    }

    /**
     * Get the default values for an entire group.
     *
     * @param string $group
     * @param bool   $short True to strip off the group name, false to include the group name in the key
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     *
     * @return array
     */
    public function getDefaultGroup($group, $short = true)
    {
        $this->getDefaultSettings()->getGroup($group, $short);
    }

    /**
     * Get all settings in a group.
     *
     * @param string $group
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     *
     * @return array
     */
    public function getGroup($group)
    {
        return $this->getSettings()->getGroup($group);
    }

    /**
     * Manually set the value for one or more settings. Note that these values are
     * temporary, they are NOT persisted. This is mainly useful for code overrides
     * or the like.
     *
     * @param array $settings
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function setTemporarySettingValues(array $settings)
    {
        // left for BC
        $this->getSettings()->setArray(array_merge($this->getSettings()->toArray(), $settings));
    }

    /**
     * Persist a new value for a setting, and update this as well.
     *
     * @param string $setting
     * @param string $value
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     *
     * @deprecated don't do this going forard. instead change the source of the setting and use the "settings_resolver" system service
     */
    public function setSetting($setting, $value)
    {
        // it is not an option in the new settings resolver to SET settings directly. This is left for BC.

        $this->db->beginTransaction();
        $this->reloadSettings();
        $old = $this->get($setting);
        try {
            if ($value !== null) {
                if ($value === true) {
                    $value = '1';
                } elseif ($value === false) {
                    $value = '0';
                }

                $this->db->executeUpdate('REPLACE INTO settings (name, value) VALUES (?, ?)', [$setting, $value]);
            } else {
                $this->db->delete('settings', ['name' => $setting]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
        $auditLog     = new AuditLog();
        $auditLogData = new AuditLogData();

        // just leave in case $old = "0" and $value = false e.g.
        if ($old === $value || ((is_numeric($old) || is_numeric($value)) && ((int) $old === (int) $value))) {
            return;
        }

        $auditLogData->setContext([])->setDiff([$setting => [$old, $value]]);
        $auditLog
            ->setAction(sprintf('settings.%s', $value !== null ? 'replace' : 'delete'))
            ->setPerformerId(App::getCurrentPerson() ? App::getCurrentPerson()->getId() : 0)
            ->setDescription(sprintf(
                    'Setting was %s via Setting::setSetting() method',
                    $value !== null ? 'replaced' : 'deleted'
                )
            )
            ->setDateCreated(new \DateTime())
            ->setObjectName(sprintf('"%s" setting', $setting))
            ->setObjectType(TypeUtils::getBaseTypeName(Setting::class))
            ->setData($auditLogData)
            ->setPerformerName(App::getCurrentPerson() ? App::getCurrentPerson()->getDisplayName() : '');

        App::get('audit_log.service')->write($auditLog);
    }

    /**
     * @return \DateTimeZone
     */
    public function getDefaultTimezone()
    {
        return $this->new_settings_resolver->getGlobalSettings()->get('default_timezone');
    }

    /**
     * @return string
     */
    public function getHelpdeskUrl()
    {
        return $this->new_settings_resolver->getGlobalSettings()->get('core.deskpro_url');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->getSettings()->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('You cannot set settings');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getSettings()->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('You cannot unset settings');
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->getSettings());
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->getSettings()->getIterator();
    }

    /**
     * @return \Application\DeskPRO\NewSettings\SettingsBag
     */
    public function getSettings()
    {
        if (null === $this->settings) {
            $this->settings = $this->new_settings_resolver->getGlobalSettings();
        }

        return $this->settings;
    }

    /**
     * @return \Application\DeskPRO\NewSettings\SettingsBag
     */
    public function getDefaultSettings()
    {
        if (null === $this->default_settings) {
            $this->default_settings = $this->new_settings_resolver->getDefaultSettings();
        }

        return $this->default_settings;
    }
}
