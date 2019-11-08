<?php

namespace DeskPRO\Bundle\VoiceBundle\Monolog;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
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
            $handler = new StreamHandler($env->getUserLogsDir().'/voice.log');
            $handler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s.u'));

            $logger->pushHandler($handler);
        }

        return $logger;
    }
}
