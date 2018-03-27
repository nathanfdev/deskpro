<?php

namespace DeskPRO\Bundle\AppBundle\Logging;

use Monolog\Handler\StreamHandler;

class DeskproFilesystemHandler extends StreamHandler
{
    public function __construct(
        $log_dir,
        $log_level,
        $kernel_name,
        $kernel_environment
    ) {
        $filename = $log_dir
            .DIRECTORY_SEPARATOR
            .$kernel_name
            .'-'
            .$kernel_environment
            .'.log'
        ;

        parent::__construct(
            $filename,
            $log_level
        );
    }
}
