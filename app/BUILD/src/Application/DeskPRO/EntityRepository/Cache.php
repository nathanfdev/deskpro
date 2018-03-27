<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class Cache extends AbstractEntityRepository
{
    public function load($id)
    {
        $data = App::getDb()->fetchColumn('SELECT data FROM cache WHERE id = ?', [$id]);

        if (!$data) {
            return false;
        }

        $data = @unserialize($data);

        if (isset($data['VALUE'])) {
            return $data['VALUE'];
        }

        return $data;
    }

    public function save($id, $data, $lifetime = null)
    {
        if (!is_array($data)) {
            $data = ['VALUE' => $data];
        }

        $data = serialize($data);

        $expire = null;
        if ($lifetime) {
            $expire = date('Y-m-d H:i:s', time() + $lifetime);
        }

        App::getDb()->executeUpdate(
            'REPLACE INTO cache SET id = ?, data = ?, date_expire = ?', [
            $id, $data, $expire,
        ]);

        return true;
    }

    public function delete($id)
    {
        return App::getDb()->executeUpdate('DELETE FROM cache WHERE id LIKE ?', [$id.'%']);
    }

    /**
     * Clean up all expired cache entries.
     */
    public function cleanExpired()
    {
        return App::getDb()->executeUpdate('DELETE FROM cache WHERE date_expire < ?', [date('Y-m-d H:i:s')]);
    }
}
