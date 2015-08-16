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
     * @var array
     */
    protected $params;

    /**
     * Constructor
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

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

        if (( ! is_object($response)) || ($client->getDebug()->lastResponseCode != 200)) {
            throw new ResponseException(__METHOD__);
        }

        $client->setSideload(null);
        return $response;
    }

    /**
     * Sends a post request
     *
     * @param Client $client
     * @param string $end_point
     * @param array  $params
     *
     * @return mixed
     * @throws ResponseException
     */
    protected function doPostRequest(Client $client, $end_point, array $params)
    {
        $response = Http::send($client, $end_point, $params, 'POST');

        if (( ! is_object($response)) || ($client->getDebug()->lastResponseCode != 200)) {
            throw new ResponseException(__METHOD__);
        }

        $client->setSideload(null);
        return $response;
    }
}
