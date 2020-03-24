<?php

namespace DeskPRO\Bundle\VoiceBundle\Monolog;

use DeskPRO\Component\Monolog\Processor\ExtraFieldsProcessor;
use DpSys\LowError\SystemErrorHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VoiceLoggerFactory.
 */
class VoiceLoggerFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return LoggerInterface
     */
    public static function createLogger(ContainerInterface $container)
    {
        $logger = new Logger('voice');
        $logger->pushProcessor(new UidProcessor());

        $env = $container->get('deskpro.app_env');
        if ($env->isQa() || $env->getConfig('logs.enable_voice_log')) {
            if (SystemErrorHandler::useSyslog()) {
                $handler = new SyslogHandler('deskpro-voice');
            } else {
                $handler = new StreamHandler($env->getUserLogsDir().'/voice.log');
            }

            $handler->setFormatter(new JsonFormatter(JsonFormatter::BATCH_MODE_NEWLINES));
            $handler->pushProcessor(new ExtraFieldsProcessor($container->get('deskpro.app_env')));

            $logger->pushHandler($handler);
        }

        return $logger;
    }
}
