<?php

namespace Application\ImportBundle\Reader\DeskPRO;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\ImportBundle\Reader\ReaderConfigInterface;
use Application\ImportBundle\Reader\ReaderFactoryInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * DeskPRO reader factory
 *
 * Class DeskPROReaderFactory
 * @package Application\ImportBundle\Reader\DeskPRO
 */
class DeskPROReaderFactory implements ReaderFactoryInterface
{
    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * Constructor
     *
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function createReader(ReaderConfigInterface $config)
    {
        if ( ! $config instanceof DeskPROConfig) {
            throw new \RuntimeException('Config expected to be instance of DeskPROConfig');
        }

        return new DeskPROReader($config, $this->container);
    }

    /**
     * @return DeskPROConfig
     * @throws \Exception
     */
    public static function getDefaultConfig()
    {
        $config = dp_get_config('deskpro_import');
        if (empty($config)) {
            throw new \Exception('DeskPRO import config is not defined');
        }

        return new DeskPROConfig(
            $config['db_host'],
            isset($config['db_port']) ? $config['db_port'] : null,
            $config['db_name'],
            $config['db_username'],
            $config['db_password'],
            @$config['start_ticket_id'] ?: 0
        );
    }
}
