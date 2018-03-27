<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class Setting extends AbstractEntityRepository
{
    /**
     * Update a database setting.
     *
     * This updates the database but not the currently loaded set of settings. If you need
     * the value to take affect immediately (this process), then use the Settings service,
     *
     * <code>$this->container->get('settings')->setSetting($name, $value);</code>
     *
     * @param string $name  The name of the setting
     * @param mixed  $value The value to set. Null means any existing value will be unset
     *
     * @return $this
     */
    public function updateSetting($name, $value)
    {
        $db = $this->_em->getConnection();

        if ($value !== null) {
            $db->executeUpdate('
                INSERT INTO settings
                    (name, value)
                VALUES
                    (?, ?)
                ON DUPLICATE KEY UPDATE
                    value = VALUES(value)
            ', [$name, $value]);
        } else {
            $db->delete('settings', ['name' => $name]);
        }

        return $this;
    }
}
