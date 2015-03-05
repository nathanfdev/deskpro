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

use Zendesk\API\Client;
use Exception;

/**
 * ZenDesk reader factory
 *
 * Class ZenDeskReaderFactory
 * @package Application\ImportBundle\Reader\ZenDesk
 */
class ZenDeskReaderFactory
{
    /**
     * Create a zenDesk reader
     *
     * @return ZenDeskReader
     * @throws Exception
     */
    public function createReader()
    {
        $dp_config = dp_get_config('zendesk_import');
        if (empty($dp_config)) {
            throw new Exception('Deskpro zendesk import config is not defined');
        }

        $config = new ZenDeskConfig($dp_config['subdomain'], $dp_config['username']);
        if (isset($dp_config['password'])) {
            $config->setPassword($dp_config['password']);
        }
        if (isset($dp_config['api_token'])) {
            $config->setApiToken($dp_config['api_token']);
        }


        $client = new Client($config->getSubdomain(), $config->getUsername());
        $client->setAuth($config->getAuthType(), $config->getAuthValue());

        return new ZenDeskReader($client);
    }
}
