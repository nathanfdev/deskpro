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

namespace Application\ImportBundle\Reader\DeskPRO;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

/**
 * DeskPRO reader factory.
 *
 * Class DeskPROReaderFactory
 */
class DeskPROReaderFactory
{
    /**
     * Create os ticket reader using deskpro config.
     *
     * @param DeskPROConfig    $config
     * @param DeskproContainer $container
     *
     * @return DeskPROReader
     */
    public static function createReader(DeskPROConfig $config, DeskproContainer $container)
    {
        return new DeskPROReader($config, $container);
    }

    /**
     * @throws \Exception
     *
     * @return DeskPROConfig
     */
    public static function getDefaultConfig()
    {
        $dp_config = dp_get_config('deskpro_import');
        if (empty($dp_config)) {
            throw new \Exception('DeskPRO import config is not defined');
        }

        return new DeskPROConfig(
            $dp_config['db_host'],
            $dp_config['db_name'],
            $dp_config['db_username'],
            $dp_config['db_password'],
            @$dp_config['start_ticket_id'] ?: 0
        );
    }
}
