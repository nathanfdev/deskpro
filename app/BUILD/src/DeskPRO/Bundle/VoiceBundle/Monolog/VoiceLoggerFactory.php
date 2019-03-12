<?php

namespace DeskPRO\Bundle\VoiceBundle\Monolog;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
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
        $env    = $container->get('deskpro.app_env');

        if ($logfile = $env->getConfig('logs.enable_voice_log')) {
            if ($logfile === true || $logfile === 1 || $logfile === '1' || $logfile === 'true') {
                $logfile = $env->getUserLogsDir().'/voice.log';
            }

            $logger->pushHandler(new StreamHandler($logfile));
        }

        return $logger;
    }
}
