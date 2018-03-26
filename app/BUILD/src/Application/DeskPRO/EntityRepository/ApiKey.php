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

    /**
     * @param \Application\DeskPRO\Entity\ApiKey $api_key
     *
     * @return array
     */
    public function getRateLimitInfo(\Application\DeskPRO\Entity\ApiKey $api_key)
    {
        $rate_limit = App::getDb()->fetchAssoc(
            '
                        SELECT *
                        FROM api_key_rate_limit
                        WHERE api_key_id = ?
                    ',
            [$api_key->id]
        );

        if ($rate_limit && $rate_limit['reset_stamp'] <= time()) {
            App::getDb()->delete(
                'api_key_rate_limit',
                [
                     'api_key_id' => $api_key->id,
                ]
            );
        }

        $interval = (int) App::getSetting('core.api_rate_limit_interval');
        if (!$rate_limit || $rate_limit['reset_stamp'] <= time()) {
            $rate_limit = [
                'api_key_id'    => $api_key->id,
                'hits'          => 0,
                'created_stamp' => time(),
                'reset_stamp'   => time() + $interval,
            ];
        }

        return $rate_limit;
    }

    /**
     * @param \Application\DeskPRO\Entity\ApiKey $api_key
     */
    public function updateRateLimit(\Application\DeskPRO\Entity\ApiKey $api_key)
    {
        $time     = time();
        $interval = (int) App::getSetting('core.api_rate_limit_interval');

        App::getDb()->executeUpdate(
            '
                        INSERT INTO api_key_rate_limit
                            (api_key_id, hits, created_stamp, reset_stamp)
                        VALUES
                            (?, 1, ?, ?)
                        ON DUPLICATE KEY UPDATE hits = hits + 1
                    ',
            [$api_key->id, $time, $time + $interval]
        );
    }
}
