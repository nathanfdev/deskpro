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

        if ($env->isQa() || $env->getConfig('logs.enable_voice_log')) {
            $logger->pushHandler(new StreamHandler($env->getUserLogsDir().'/voice.log'));
        }

        return $logger;
    }
}
