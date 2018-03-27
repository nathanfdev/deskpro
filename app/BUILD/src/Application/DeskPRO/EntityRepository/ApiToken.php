<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class ApiToken extends AbstractEntityRepository
{
    /**
     * Find an API key based off of a key string. A key string is: "id:code".
     *
     * @param string $api_string
     *
     * @return \Application\DeskPRO\Entity\ApiToken
     */
    public function findByTokenString($token_string)
    {
        if (strpos($token_string, ':') === false) {
            return;
        }

        list($id, $token) = explode(':', $token_string, 2);

        $token_obj = $this->find($id);
        if (!$token_obj) {
            return;
        }
        if ($token_obj->token != $token) {
            return;
        }

        return $token_obj;
    }

    public function getTokenForPerson(\Application\DeskPRO\Entity\Person $person)
    {
        return $this->getEntityManager()->createQuery("
            SELECT t
            FROM DeskPRO:ApiToken t
            WHERE t.person = ?0 AND t.scope = 'client'
        ")->setParameters([$person])->setMaxResults(1)->getOneOrNullResult();
    }

    public function getRateLimitInfo(\Application\DeskPRO\Entity\ApiToken $api_token)
    {
        $rate_limit = App::getDb()->fetchAssoc('
            SELECT *
            FROM api_token_rate_limit
            WHERE api_token_id = ?
        ', [$api_token->id]);

        if ($rate_limit && $rate_limit['reset_stamp'] <= time()) {
            App::getDb()->delete('api_token_rate_limit', [
                'api_token_id' => $api_token->id,
            ]);
        }

        $interval = (int) App::getSetting('core.api_rate_limit_interval');
        if (!$rate_limit || $rate_limit['reset_stamp'] <= time()) {
            $rate_limit = [
                'api_token_id'  => $api_token->id,
                'hits'          => 0,
                'created_stamp' => time(),
                'reset_stamp'   => time() + $interval,
            ];
        }

        return $rate_limit;
    }

    public function updateRateLimit(\Application\DeskPRO\Entity\ApiToken $api_token)
    {
        $time = time();

        $interval = (int) App::getSetting('core.api_rate_limit_interval');
        App::getDb()->executeUpdate('
            INSERT INTO api_token_rate_limit
                (api_token_id, hits, created_stamp, reset_stamp)
            VALUES
                (?, 1, ?, ?)
            ON DUPLICATE KEY UPDATE hits = hits + 1
        ', [$api_token->id, $time, $time + $interval]);
    }
}
