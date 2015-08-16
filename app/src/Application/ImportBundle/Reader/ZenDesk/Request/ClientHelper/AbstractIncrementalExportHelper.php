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

namespace Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper;

use Zendesk\API\Client;
use Zendesk\API\Http;
use Zendesk\API\MissingParametersException;
use Zendesk\API\ResponseException;

/**
 * Base ZenDesk incremental export request client helper
 *
 * Class AbstractIncrementalExportHelper
 * @package Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper
 *
 * @see https://developer.zendesk.com/rest_api/docs/core/incremental_export
 * @see https://support.zendesk.com/hc/en-us/articles/204396193-New-Incremental-APIs-now-available-to-all-accounts?preview%5Btheme_id%5D=202201216&use_theme_settings=false
 */
abstract class AbstractIncrementalExportHelper extends AbstractHelper
{
    /**
     * Incremental exports with a supplied start_time
     * Not implemented in zendesk_api_client_php yet
     *
     * @param Client $client
     * @param string $type
     * @param array  $params
     * @param string $api_group
     *
     * @throws MissingParametersException
     * @throws ResponseException
     *
     * @return \stdClass
     */
    protected function incrementalExport(Client $client, $type, array $params, $api_group = '')
    {
        if ( ! $params['start_time']) {
            throw new MissingParametersException(__METHOD__, array('start_time'));
        }

        $request_url = $api_group . sprintf('incremental/%s.json?start_time=%s', $type, $params['start_time']);
        $end_point   = Http::prepare($request_url);

        return $this->doGetRequest($client, $end_point);
    }
}
