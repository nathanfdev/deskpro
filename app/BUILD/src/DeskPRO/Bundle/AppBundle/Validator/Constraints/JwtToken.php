<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class JwtToken.
 */
class JwtToken extends Constraint
{
    const MISSING_JWT_TOKEN = 'missing_jwt_token';
    const INVALID_JWT_TOKEN = 'invalid_jwt_token';
    const EXPIRED_JWT_TOKEN = 'expired_jwt_token';

    public $missingMessage = 'JWT token is required.';
    public $invalidMessage = 'This value is not a valid JWT token.';
    public $expiredMessage = 'JWT token is expired.';

    /**
     * @var string
     */
    public $secret;

    /**
     * @var string
     */
    public $algo;

    /**
     * @var bool
     */
    public $required = false;
}
