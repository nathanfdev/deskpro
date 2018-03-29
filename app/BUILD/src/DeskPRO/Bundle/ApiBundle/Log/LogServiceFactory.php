<?php

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
