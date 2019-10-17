<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle\PermissionStrategy;

use Application\LegacyApiBundle\ApiUser;

/**
 * Class UserTypePermission.
 */
class UserTypePermission implements PermissionStrategyInterface
{
    const ADMIN = 'admin';
    const AGENT = 'agent';

    /**
     * @var string
     */
    private $type;

    /**
     * Constructor.
     *
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function userHasPermission(ApiUser $apiUser, $contextInfo = null)
    {
        $person = $apiUser->person;
        if (!$person) {
            return false;
        }

        if ($this->type === self::ADMIN) {
            if ($person->isActiveAgent() && $person->canAdmin()) {
                return true;
            }
        } elseif ($this->type === self::AGENT) {
            if ($person->isActiveAgent()) {
                return true;
            }
        }

        return false;
    }
}
