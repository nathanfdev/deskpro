<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Brand as BrandEntity;

class BrandSetting extends AbstractEntityRepository
{
    /**
     * Update a database brand setting.
     *
     * @param string $name  The name of the setting
     * @param mixed  $value The value to set. Null means any existing value will be unset
     * @param Brand  $brand
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @return $this
     */
    public function updateSetting($name, $value, BrandEntity $brand)
    {
        $db = $this->_em->getConnection();

        if ($value !== null) {
            $db->executeUpdate('
                INSERT INTO settings_brand
                    (name, value, brand_id)
                VALUES
                    (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    value = VALUES(value)
            ', [$name, $value, $brand->getId()]);
        } else {
            $db->delete('settings_brand', ['name' => $name, 'brand_id' => $brand->getId()]);
        }

        return $this;
    }
}
