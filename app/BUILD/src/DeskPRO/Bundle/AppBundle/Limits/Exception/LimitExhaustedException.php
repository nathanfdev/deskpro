<?php

namespace DeskPRO\Bundle\AppBundle\Limits\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class LimitExhaustedException.
 */
class LimitExhaustedException extends \RuntimeException
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('', Response::HTTP_FORBIDDEN);
    }
}
