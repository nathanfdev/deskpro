<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use DeskPRO\Bundle\ApiBundle\Log\Finder\FinderInterface;
use DeskPRO\Bundle\ApiBundle\Log\Serializer\SerializerInterface;
use DeskPRO\Bundle\ApiBundle\Log\Writer\WriterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LogServiceFactory.
 */
class LogServiceFactory
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
        $this->settings_resolver->setVirtual(
            'api_log.finder.type',
            function ($global) {
                return $global['api_log.writer.type'];
            });
        $this->settings_resolver->getGlobalSettings(true);
    }

    /**
     * @param null $type
     *
     * @return WriterInterface
     */
    public function createWriter($type = null)
    {
        return $this->create('api_log.writer', 'writer', $type);
    }

    /**
     * @param null $type
     *
     * @return FinderInterface
     */
    public function createFinder($type = null)
    {
        return $this->create('api_log.finder', 'finder', $type);
    }

    /**
     * @param null $type
     *
     * @return SerializerInterface
     */
    public function createSerializer($type = null)
    {
        return $this->create('api_log.writer.file.serializer', 'serializer', $type);
    }

    /**
     * @param $prefix
     * @param $object_type
     * @param string $type
     *
     * @return FinderInterface|WriterInterface|SerializerInterface
     */
    protected function create($prefix, $object_type, $type = null)
    {
        if (!$type) {
            $type = $this->settings_resolver->getGlobalSettings()->get(sprintf('%s.type', $prefix));
        }

        $name = sprintf('%s.%s', $prefix, $type);

        if ($this->container->has($name)) {
            return $this->container->get($name);
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Couldn\'t instantiate %s with type [ %s ] for api_log file writer',
                    $object_type,
                    $name
                )
            );
        }
    }
}
