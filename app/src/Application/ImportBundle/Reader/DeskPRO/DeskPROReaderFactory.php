<?php

namespace Application\ImportBundle\Reader\DeskPRO;

use Symfony\Component\DependencyInjection\Container;

/**
 * DeskPRO reader factory
 *
 * Class DeskPROReaderFactory
 * @package Application\ImportBundle\Reader\DeskPRO
 */
class DeskPROReaderFactory
{
    /**
     * Create os ticket reader using deskpro config
     *
     * @param Config    $config
     * @param Container $container
     *
     * @return DeskPROReader
     */
    public static function createReader(Config $config, Container $container)
    {
        $config = $config ?: self::getDefaultConfig();
        return new DeskPROReader($config, $container);
    }

    /**
     * @return Config
     * @throws \Exception
     */
    public static function getDefaultConfig()
    {
        $dp_config = dp_get_config('deskpro_import');
        if (empty($dp_config)) {
            throw new \Exception('DeskPRO import config is not defined');
        }

        return new Config(
            $dp_config['db_host'],
            $dp_config['db_name'],
            $dp_config['db_username'],
            $dp_config['db_password'],
            @$dp_config['start_ticket_id'] ?: 0
        );
    }
}
