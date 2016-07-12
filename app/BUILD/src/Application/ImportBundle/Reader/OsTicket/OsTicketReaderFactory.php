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

namespace Application\ImportBundle\Reader\OsTicket;

use Application\ImportBundle\Reader\ReaderConfigInterface;
use Application\ImportBundle\Reader\ReaderFactoryInterface;
use Exception;

/**
 * Os ticket reader factory.
 *
 * Class OsTicketReaderFactory
 */
class OsTicketReaderFactory implements ReaderFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createReader(ReaderConfigInterface $config)
    {
        $config = $config ?: self::getDefaultConfig();
        if (!$config instanceof OsTicketConfig) {
            throw new \RuntimeException('Config expected to be instance of OsTicketConfig');
        }

        return new OsTicketReader($config);
    }

    /**
     * @throws Exception
     *
     * @return \Application\ImportBundle\Reader\OsTicket\OsTicketConfig
     */
    public static function getDefaultConfig()
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;
        $config = $DP_ENV->getConfig('import.osticket_import');

        if (empty($config)) {
            throw new \Exception('OsTicket import config is not defined');
        }

        return new OsTicketConfig(
            $config['db_host'],
            isset($config['db_port']) ? $config['db_port'] : null,
            $config['db_name'],
            $config['db_username'],
            $config['db_password']
        );
    }
}
