<?php

namespace Application\ImportBundle\Reader\OsTicket;

use Application\ImportBundle\Reader\ReaderConfigInterface;
use Application\ImportBundle\Reader\ReaderFactoryInterface;
use Exception;

/**
 * Os ticket reader factory
 *
 * Class OsTicketReaderFactory
 * @package Application\ImportBundle\Reader\OsTicket
 */
class OsTicketReaderFactory implements ReaderFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createReader(ReaderConfigInterface $config)
    {
        $config = $config ? : self::getDefaultConfig();
        if ( ! $config instanceof OsTicketConfig) {
            throw new \RuntimeException('Config expected to be instance of OsTicketConfig');
        }

        return new OsTicketReader($config);
    }

    /**
     * @return \Application\ImportBundle\Reader\OsTicket\OsTicketConfig
     * @throws Exception
     */
    public static function getDefaultConfig()
    {
        $config = dp_get_config('osticket_import');
        if (empty($config)) {
            throw new \Exception('OsTicket import config is not defined');
        }

        return new OsTicketConfig(
            $config['db_host'],
            $config['db_name'],
            $config['db_username'],
            $config['db_password']
        );
    }
}
