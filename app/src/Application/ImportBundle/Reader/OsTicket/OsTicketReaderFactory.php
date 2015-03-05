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
     * Create os ticket reader using deskpro config
     *
     * @return OsTicketReader
     * @throws Exception
     */
    public function createReaderByDeskproConfig()
    {
        $dp_config = dp_get_config('osticket_import');
        if (empty($dp_config)) {
            throw new Exception('Deskpro os ticket import config is not defined');
        }

        $db_host     = $dp_config['db_host'];
        $db_name     = $dp_config['db_name'];
        $db_username = $dp_config['db_username'];
        $db_password = $dp_config['db_password'];

        return new OsTicketReader(new LazyConnectionWrapper(
            sprintf('mysql:dbname=%s;host=%s', $db_name, $db_host),
            $db_username,
            $db_password
        ));
    }
}
