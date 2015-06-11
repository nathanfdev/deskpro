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
use Psr\Log\LoggerInterface;
use Zendesk\API;
use RuntimeException;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param API\Client      $client
     * @param LoggerInterface $logger
     */
    public function __construct(API\Client $client, LoggerInterface $logger = null)
    {
        $this->client = $client;
        $this->logger = $logger;
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
     * @param int $is_retry
     *
     * @return \stdClass
     *
     * @throws RetryAfterException
     * @throws API\ResponseException
     */
    private function doRequest(ClientHelper\ClientHelperInterface $request, $is_retry = 0)
    {
        try {
            $response = $request->request($this->client);
            return $response;
        } catch (API\ResponseException $e) {
            if ($this->client->getDebug()) {
                $debug = $this->client->getDebug();

                if ($this->logger) {
                    $this->logger->error(sprintf("[%s] (%s) %s -- %s", $e->getCode(), get_class($e), $e->getMessage(), $debug ? print_r($debug) : "nodebug"));
                }

                switch ($debug->lastResponseCode) {
                    case ZenDeskReaderInterface::CODE_UNAUTHORIZED:
                        throw new RuntimeException(
                            'Unable to connect, check ZenDesk exporter credentials',
                            $e->getCode(), $e
                        );

                    case ZenDeskReaderInterface::CODE_TOO_MANY_REQUESTS:
                        $secs = RetryAfterException::parseRetryAfterTimeout($debug->lastResponseHeaders);
                        if ($is_retry) {
                            throw new RetryAfterException(
                                $e->getMessage(),
                                $secs
                            );
                        } else {
                            if ($this->logger) {
                                $this->logger->info("Hit request limit, sleeping for $secs seconds");
                            }
                            sleep($secs+1);
                            return $this->doRequest($request, true);
                        }

                    case ZenDeskReaderInterface::CODE_UN_PROCESSABLE_ENTITY:
                        // nothing to do

                        break;

                    default:
                        if ($is_retry++ < 4) {
                            if ($this->logger) {
                                $this->logger->error("Unknown API request error. Will retry.");
                            }
                            sleep(2+$is_retry);
                            return $this->doRequest($request, true);
                        }
                        throw $e;
                }
            }
        }

        return null;
    }
}
