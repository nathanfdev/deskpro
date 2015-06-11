<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Reader\ZenDesk;

use Application\ImportBundle\Reader\ZenDesk\Request\JsonMockAdapter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Zendesk\API\Client;
use Exception;
use DateTime;

/**
 * ZenDesk reader factory
 *
 * Class ZenDeskReaderFactory
 * @package Application\ImportBundle\Reader\ZenDesk
 */
class ZenDeskReaderFactory
{
    /**
     * Create a ZenDesk reader
     *
     * @return ZenDeskReader
     * @throws Exception
     */
    public static function createReaderByDeskPROConfig()
    {
        $config = self::getZenDeskConfig();
        $client = self::createClient($config);

        return new ZenDeskReader(
            new Request\RequestCacheAdapter(
                new Request\RequestClientAdapter($client)
            ),

            $config
        );
    }

    /**
     * @param ZenDeskConfig $config
     * @return ZenDeskReader
     */
    public static function createReader(ZenDeskConfig $config)
    {
        $client = self::createClient($config);
        $logger = new Logger('zendesk');

        $formatter = new LineFormatter();
        $formatter->ignoreEmptyContextAndExtra(true);

        $handler = new StreamHandler(dp_get_log_dir() . '/export_zendesk.log');
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        return new ZenDeskReader(
            new Request\RequestCacheAdapter(
                new Request\RequestClientAdapter($client, $logger)
            ),

            $config
        );
    }

    /**
     * @param ZenDeskConfig $config
     * @return ZenDeskReader
     */
    public static function createMockReader(ZenDeskConfig $config)
    {
        $adapter = new JsonMockAdapter();
        $adapter
            ->addTicketsIncrementalExportResponse(dp_get_data_dir() . '/import/lamoda/tickets.json')
            ->addPeopleFindResponse(dp_get_data_dir() . '/import/lamoda/users.json')
            ->addPeopleFindResponse(dp_get_data_dir() . '/import/lamoda/users2.json')
            ->addOrganizationFindResponse(dp_get_data_dir() . '/import/lamoda/organization.json')
        ;

        return new ZenDeskReader(new Request\RequestCacheAdapter($adapter), $config);
    }

    /**
     * Create a ZenDesk fixtures collection
     *
     * @return Fixtures\Collection
     * @throws Exception
     */
    public static function createFixturesByDeskproConfig()
    {
        $config = self::getZenDeskConfig();
        $client = self::createClient($config);

        $collection = new Fixtures\Collection();
        $collection
            ->attach(new Fixtures\People($client))
            ->attach(new Fixtures\Tickets($client))
        ;

        return $collection;
    }

    /**
     * Create ZenDesk client
     *
     * @param ZenDeskConfig $config
     *
     * @return Client
     * @throws Exception
     */
    private static function createClient(ZenDeskConfig $config)
    {
        $client = new Client($config->getSubdomain(), $config->getUsername());
        $client->setAuth($config->getAuthType(), $config->getAuthValue());

        return $client;
    }

    /**
     * Create ZenDesk client config
     *
     * @return ZenDeskConfig
     * @throws Exception
     */
    public static function getZenDeskConfig()
    {
        $dp_config = dp_get_config('zendesk_import');
        if (empty($dp_config)) {
            throw new Exception('DeskPRO zendesk import config is not defined');
        }

        $config = new ZenDeskConfig(
            $dp_config['subdomain'],
            $dp_config['username'],
            new DateTime($dp_config['initial_time'])
        );

        if (isset($dp_config['password'])) {
            $config->setPassword($dp_config['password']);
        }
        if (isset($dp_config['api_token'])) {
            $config->setApiToken($dp_config['api_token']);
        }

        return $config;
    }
}
