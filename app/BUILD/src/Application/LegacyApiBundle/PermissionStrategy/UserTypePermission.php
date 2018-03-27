<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle\PermissionStrategy;

use Application\LegacyApiBundle\ApiUser;

class UserTypePermission implements PermissionStrategyInterface
{
    const ADMIN = 'admin';
    const AGENT = 'agent';
    const USER  = 'user';

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function userHasPermission(ApiUser $api_user, $context_info = null)
    {
        $person = $api_user->person;
        if (!$person) {
            return false;
        }

        switch ($this->type) {
            case self::ADMIN: if ($person->is_agent && $person->can_admin) {
                return true;
            } break;
            case self::AGENT: if ($person->is_agent) {
                return true;
            } break;
            case self::USER:  if (!$person->is_deleted || !$person->is_disabled) {
                return true;
            } break;
        }

        return false;
    }
}
