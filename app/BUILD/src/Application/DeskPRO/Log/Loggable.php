<?php

namespace Application\DeskPRO\Log;

interface Loggable
{
    /**
     * @return string log message
     */
    public function __toString();

    /**
     * @return array log record context
     */
    public function context();
}
