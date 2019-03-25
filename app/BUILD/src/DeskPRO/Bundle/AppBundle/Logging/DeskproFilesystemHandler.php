<?php

namespace DeskPRO\Bundle\AppBundle\Logging;

use DpSys\LowError\SystemErrorHandler;
use Monolog\Handler\AbstractHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;

class DeskproFilesystemHandler extends AbstractHandler
{
    /**
     * @var AbstractHandler
     */
    private $h;

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

        if (SystemErrorHandler::useSyslog()) {
            $this->h = new SyslogHandler(
                "deskpro-$kernel_name-$kernel_environment",
                LOG_USER,
                $log_level
            );
        } else {
            $this->h = new StreamHandler($filename, $log_level);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        try {
            return $this->h->handle($record);
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        try {
            $this->h->handleBatch($records);
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }
    }
}
