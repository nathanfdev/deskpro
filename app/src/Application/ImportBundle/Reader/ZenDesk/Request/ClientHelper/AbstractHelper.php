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

use Symfony\Component\HttpFoundation\Response;
use Zendesk\API\Client;
use Zendesk\API\Http;
use Zendesk\API\MissingParametersException;
use Zendesk\API\ResponseException;

/**
 * Base ZenDesk request client helper
 *
 * Class AbstractHelper
 * @package Application\ImportBundle\Reader\ZenDesk\Request\ClientHelper
 */
abstract class AbstractHelper implements ClientHelperInterface
{
    /**
     * Sends a get request
     * Some of end points are not implemented in ZenDesk api client library
     *
     * @param Client $client
     * @param string $end_point
     *
     * @return mixed
     * @throws ResponseException
     */
    protected function doGetRequest(Client $client, $end_point)
    {
        $response = Http::send($client, $end_point);

        if ( ! is_object($response) || $client->getDebug()->lastResponseCode != 200) {
            throw new ResponseException(__METHOD__);
        }

        $client->setSideload(null);
        return $response;
    }

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
     *
     * @see https://developer.zendesk.com/rest_api/docs/core/incremental_export
     * @see https://support.zendesk.com/hc/en-us/articles/204396193-New-Incremental-APIs-now-available-to-all-accounts?preview%5Btheme_id%5D=202201216&use_theme_settings=false
     */
    protected function doIncrementalExportRequest(Client $client, $type, array $params, $api_group = '')
    {
        if ( ! $params['start_time']) {
            throw new MissingParametersException(__METHOD__, array('start_time'));
        }

        $request_url = rtrim($api_group, '/') . '/' . sprintf('incremental/%s.json?start_time=%s', $type, $params['start_time']);
        $end_point   = Http::prepare($request_url);

        return $this->doGetRequest($client, $end_point);
    }

    /**
     * Sends a post request
     *
     * @param Client $client
     * @param string $end_point
     * @param array  $params
     * @param string $content_type
     *
     * @return mixed
     * @throws ResponseException
     */
    protected function doPostRequest(Client $client, $end_point, array $params, $content_type = 'application/json')
    {
        $response      = Http::send($client, $end_point, $params, 'POST', $content_type);
        $success_codes = array(
            Response::HTTP_OK,
            Response::HTTP_CREATED,
        );

        if ( ! is_object($response) || ! in_array($client->getDebug()->lastResponseCode, $success_codes)) {
            throw new ResponseException($end_point);
        }

        $client->setSideload(null);
        return $response;
    }

    /**
     * Sends a delete request
     *
     * @param Client $client
     * @param string $end_point
     *
     * @return mixed
     * @throws ResponseException
     */
    protected function doDeleteRequest(Client $client, $end_point)
    {
        $response      = Http::send($client, $end_point, null, 'DELETE');
        $success_codes = array(
            Response::HTTP_OK,
            Response::HTTP_NO_CONTENT,
        );

        if ( ! in_array($client->getDebug()->lastResponseCode, $success_codes)) {
            throw new ResponseException($end_point);
        }

        $client->setSideload(null);
        return $response;
    }
}
