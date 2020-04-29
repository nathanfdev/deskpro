<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class ApiKey extends AbstractEntityRepository
{
    /**
     * Find an API key based off of a key string. A key string is: "id:code".
     *
     * @param string $key_string
     *
     * @return \Application\DeskPRO\Entity\ApiKey
     */
    public function findByKeyString($key_string)
    {
        if (strpos($key_string, ':') === false) {
            return;
        }

        list($id, $code) = explode(':', $key_string, 2);

        $apikey = $this->find($id);
        if (!$apikey) {
            return;
        }
        if ($apikey['code'] != $code) {
            return;
        }

        return $apikey;
    }

    /**
     * @return ApiKey[]
     */
    public function getAllApiKeys()
    {
        return $this->_em->createQuery('
            SELECT k
            FROM DeskPRO:ApiKey k
            LEFT JOIN k.person p
            ORDER BY p.name
        ')->execute();
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    public function getApiKeyTitles(array $ids = null)
    {
        $output = [];
        foreach ($this->getAllApiKeys() as $key) {
            if ($ids === null || in_array($key->id, $ids)) {
                $output[$key->id] = ($key->person ? $key->person->display_name : 'Super User')
                    .($key->note ? " ($key->note)" : '');
            }
        }

        return $output;
    }

    /**
     * @return mixed
     */
    public function countApiKeys()
    {
        return App::getDb()->fetchColumn('
            SELECT COUNT(*)
            FROM api_keys
        ');
    }
}
