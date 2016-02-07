<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Log;

use Application\DeskPRO\NewSettings\SettingsResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ApiLoggerFactory.
 */
class ApiLoggerFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SettingsResolver
     */
    protected $settings_resolver;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container         = $container;
        $this->settings_resolver = $this->container->get('settings_resolver');
    }

    public function createLogger()
    {
        $type       = $this->settings_resolver->getGlobalSettings()->get('api_logger.type');
        $loggerName = sprintf('api_logger.%s', $type);
        if ($this->container->has($loggerName)) {
            return $this->container->get($loggerName);
        } else {
            throw new \InvalidArgumentException(sprintf('Couldn\'t instantiate logger with type [ %s ]', $loggerName));
        }
    }

    public function createSerializer()
    {
        $type           = $this->settings_resolver->getGlobalSettings()->get('api_logger.file.serializer.type');
        $serializerName = sprintf('api_logger.file.serializer.%s', $type);
        if ($this->container->has($serializerName)) {
            return $this->container->get($serializerName);
        } else {
            throw new \InvalidArgumentException(sprintf('Couldn\'t instantiate logger serializer with type [ %s ]', $serializerName));
        }
    }
}
