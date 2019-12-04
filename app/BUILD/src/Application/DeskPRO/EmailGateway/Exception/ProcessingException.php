<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Exception;

class ProcessingException extends \Exception
{
    const MEMORY_LIMIT = 100;
    const EMAIL_ACCOUNT_NOT_FOUND = 200;
}
