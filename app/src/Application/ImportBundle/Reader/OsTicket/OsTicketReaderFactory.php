<?php

namespace Application\ImportBundle\Reader\OsTicket;

use Application\ImportBundle\Reader\OsTicket\OsTicketConfig;
use Exception;

/**
 * Os ticket reader factory.
 *
 * Class OsTicketReaderFactory
 */
class OsTicketReaderFactory
{
    /**
     * Create os ticket reader using deskpro config.
     *
     * @throws Exception
     * @return OsTicketReader
     *
     */
    public static function createReader(OsTicketConfig $config)
    {
        $config = $config ?: self::getDefaultConfig();

        return new OsTicketReader($config);
    }

    public static function getDefaultConfig()
    {
        $dp_config = dp_get_config('osticket_import');
        if (empty($dp_config)) {
            throw new \Exception('DeskPRO os ticket import config is not defined');
        }

        return new OsTicketConfig(
            $dp_config['db_host'],
            $dp_config['db_name'],
            $dp_config['db_username'],
            $dp_config['db_password']
        );
    }
}
