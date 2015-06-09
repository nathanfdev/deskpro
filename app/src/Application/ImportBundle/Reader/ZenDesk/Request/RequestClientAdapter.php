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

namespace Application\ImportBundle\Reader\ZenDesk\Request;

use Application\ImportBundle\Reader\ZenDesk\RetryAfterException;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use Zendesk\API;

/**
 * ZenDesk API request adapter via ZenDesk client vendor
 *
 * Class RequestClientAdapter
 * @package Application\ImportBundle\Reader\ZenDesk\Request
 */
final class RequestClientAdapter implements RequestAdapterInterface
{
    /**
     * @var API\Client
     */
    private $client;

    /**
     * Constructor
     *
     * @param API\Client $client
     */
    public function __construct(API\Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function doPeopleIncrementalExportRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\PeopleIncrementalExport($params));
    }

    /**
     * {@inheritdoc}
     */
    public function doPeopleFindRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\PeopleFind($params));
    }

    /**
     * {@inheritdoc}
     */
    public function doOrganizationFindRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\OrganizationFind($params));
    }

    /**
     * {@inheritdoc}
     */
    public function doTicketsIncrementalExportRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\TicketsIncrementalExport($params));
    }

    /**
     * Do API request
     *
     * @param ClientHelper\ClientHelperInterface $request
     *
     * @return \stdClass
     *
     * @throws RetryAfterException
     * @throws API\ResponseException
     */
    private function doRequest(ClientHelper\ClientHelperInterface $request)
    {
        try {
            return $request->request($this->client);

        } catch (API\ResponseException $e) {
            if ($this->client->getDebug()) {
                $debug = $this->client->getDebug();

                switch ($debug->lastResponseCode) {
                    // Handle HTTP 429 Too Many Requests response
                    case ZenDeskReaderInterface::CODE_TOO_MANY_REQUESTS:
                        throw new RetryAfterException(
                            $e->getMessage(),
                            RetryAfterException::parseRetryAfterTimeout($debug->lastResponseHeaders)
                        );
                    case ZenDeskReaderInterface::CODE_UN_PROCESSABLE_ENTITY:
                        // nothing to do

                        break;

                    default:
                        throw $e;
                }
            }
        }

        return null;
    }
}
