<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle;

use Application\DeskPRO\Entity\ApiKey;

class ApiUser
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    public $person;

    /**
     * @var \Application\DeskPRO\Entity\Session
     */
    public $session;

    /**
     * @var ApiKey
     */
    public $api_key;

    /**
     * @var \Application\DeskPRO\Entity\ApiToken
     */
    public $api_token;

    /**
     * @var string
     */
    public $request_token;

    public function isSuperAdmin()
    {
        return $this->api_key
            ? $this->api_key->isFlagSet(ApiKey::FLAG_SUPER_KEY)
            : false;
    }
}
