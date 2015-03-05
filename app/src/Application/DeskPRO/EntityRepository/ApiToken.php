<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;
use Application\DeskPRO\App;

class ApiToken extends AbstractEntityRepository
{
    /**
     * Find an API key based off of a key string. A key string is: "id:code"
     *
     * @param string $api_string
     *
     * @return \Application\DeskPRO\Entity\ApiToken
     */
    public function findByTokenString($token_string)
    {
        if (strpos($token_string, ':') === false) return null;

        list ($id, $token) = explode(':', $token_string, 2);

        $token_obj = $this->find($id);
        if (!$token_obj) return null;
        if ($token_obj->token != $token) return null;
        return $token_obj;
    }

    public function getTokenForPerson(\Application\DeskPRO\Entity\Person $person)
    {
        return $this->getEntityManager()->createQuery("
            SELECT t
            FROM DeskPRO:ApiToken t
            WHERE t.person = ?0 AND t.scope = 'client'
        ")->setParameters(array($person))->getOneOrNullResult();
    }

    public function getRateLimitInfo(\Application\DeskPRO\Entity\ApiToken $api_token)
    {
        $rate_limit = App::getDb()->fetchAssoc("
            SELECT *
            FROM api_token_rate_limit
            WHERE api_token_id = ?
        ", array($api_token->id));

        if ($rate_limit && $rate_limit['reset_stamp'] <= time()) {
            App::getDb()->delete('api_token_rate_limit', array(
                'api_token_id' => $api_token->id
            ));
        }

        $interval = (int) App::getSetting('core.api_rate_limit_interval');
        if (!$rate_limit || $rate_limit['reset_stamp'] <= time()) {
            $rate_limit = array(
                'api_token_id' => $api_token->id,
                'hits' => 0,
                'created_stamp' => time(),
                'reset_stamp' => time() + $interval
            );
        }

        return $rate_limit;
    }

    public function updateRateLimit(\Application\DeskPRO\Entity\ApiToken $api_token)
    {
        $time = time();

        $interval = (int) App::getSetting('core.api_rate_limit_interval');
        App::getDb()->executeUpdate("
            INSERT INTO api_token_rate_limit
                (api_token_id, hits, created_stamp, reset_stamp)
            VALUES
                (?, 1, ?, ?)
            ON DUPLICATE KEY UPDATE hits = hits + 1
        ", array($api_token->id, $time, $time + $interval));
    }
}
