<?php

namespace Application\ImportBundle\Reader\OsTicket;

use Exception;

/**
 * Os ticket reader factory
 *
 * Class OsTicketReaderFactory
 * @package Application\ImportBundle\Reader\OsTicket
 */
class OsTicketReaderFactory
{
    /**
     * Create OsTicket reader using DeskPRO config
     *
     * @param OsTicketConfig $config
     *
     * @return OsTicketReader
     * @throws Exception
     */
    public static function createReader(OsTicketConfig $config)
    {
        $config = $config ? : self::getDefaultConfig();

        return new OsTicketReader($config);
    }

    /**
     * @return \Application\ImportBundle\Reader\OsTicket\OsTicketConfig
     * @throws Exception
     */
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
