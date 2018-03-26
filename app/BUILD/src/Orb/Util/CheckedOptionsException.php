<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

class CheckedOptionsException extends \Exception
{
    /** @var array */
    public $validator_errors;
    /** @var array */
    public $validator_error_info;

    public function __construct($message, array $errors, array $error_info, $code = 0, $previous = null)
    {
        $this->validator_errors     = $errors;
        $this->validator_error_info = $error_info;

        parent::__construct($message, $code, $previous);
    }
}
