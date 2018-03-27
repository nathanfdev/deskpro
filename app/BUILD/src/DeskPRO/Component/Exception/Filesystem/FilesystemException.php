<?php

namespace DeskPRO\Component\Exception\Filesystem;

use Exception;

class FilesystemException extends \RuntimeException
{
    /**
     * @var string|null
     */
    private $operationErrorMessage;

    /**
     * {@inheritdoc}
     *
     * @param string|null $operationErrorMessage The specific filesystem error message (e.g. 'no permission to write to xyz')
     */
    public function __construct($message, $code = 0, Exception $previous = null, $operationErrorMessage = null)
    {
        parent::__construct($message, $code, $previous);
        $this->operationErrorMessage = $operationErrorMessage;
    }

    /**
     * @return string|null
     */
    public function getOperationErrorMessage()
    {
        return $this->operationErrorMessage;
    }
}
