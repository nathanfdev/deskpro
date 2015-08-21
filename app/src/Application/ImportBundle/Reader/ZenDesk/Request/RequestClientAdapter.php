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
use Exception;

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
     * @var array
     */
    private $options;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $was_request = false;

    /**
     * Constructor
     *
     * @param API\Client      $client
     * @param array           $options
     * @param LoggerInterface $logger
     */
    public function __construct(API\Client $client, array $options = array(), LoggerInterface $logger = null)
    {
        $this->client  = $client;
        $this->options = $options;
        $this->logger  = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function doPeopleIncrementalExportRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\CoreAPI\PeopleIncrementalExport($params));
    }

    /**
     * {@inheritdoc}
     */
    public function doPeopleFindRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\CoreAPI\PeopleFind($params));
    }

    /**
     * {@inheritdoc}
     */
    public function doOrganizationFindRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\CoreAPI\OrganizationFind($params));
    }

    /**
     * {@inheritdoc}
     */
    public function doTicketsIncrementalExportRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\CoreAPI\TicketsIncrementalExport($params));
    }

    /**
     * {@inheritdoc}
     */
    public function doTicketCommentsFindAllRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\CoreAPI\TicketCommentsFindAll($params));
    }

    /**
     * {@inheritdoc}
     */
    public function doArticleIncrementalExportRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\HelpCenter\ArticleIncrementalExport($params));
    }

    /**
     * {@inheritdoc}
     */
    public function doArticleCommentsFindAllRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\HelpCenter\ArticleCommentsFindAll($params));
    }

    /**
     * {@inheritdoc}
     */
    public function doArticleAttachmentsFindAllRequest(array $params = array())
    {
        return $this->doRequest(new ClientHelper\HelpCenter\ArticleAttachmentsFindAll($params));
    }

    /**
     * Do API request
     *
     * @param ClientHelper\ClientHelperInterface $request
     * @param int                                $retry_attempt
     *
     * @return \stdClass
     *
     * @throws RetryAfterException
     * @throws API\ResponseException
     */
    public function doRequest(ClientHelper\ClientHelperInterface $request, $retry_attempt = 0)
    {
        try {
            API\Http::$curl = new CurlRequest(null, $this->options);

            $response = $request->request($this->client);
            $this->was_request = true;

            return $response;

        } catch (API\ResponseException $e) {
            if ($this->client->getDebug()) {
                $debug = $this->client->getDebug();

                if ($this->logger) {
                    $this->logger->error($debug->__toString());
                }

                switch ($debug->lastResponseCode) {
                    case ZenDeskReaderInterface::CODE_UNAUTHORIZED:
                        throw new RuntimeException(
                            'Unable to connect, check ZenDesk exporter credentials',
                            $e->getCode(), $e
                        );

                    case ZenDeskReaderInterface::CODE_TOO_MANY_REQUESTS:
                        $timeout = RetryAfterException::parseRetryAfterTimeout($debug->lastResponseHeaders);
                        if ($this->logger) {
                            $this->logger->info("Hit request limit, sleeping for $timeout seconds");
                        }

                        return $this->retry(
                            $request,
                            $retry_attempt,
                            new RetryAfterException($e->getMessage(), $timeout),
                            $timeout
                        );

                    case ZenDeskReaderInterface::CODE_UN_PROCESSABLE_ENTITY:
                        // nothing to do

                        break;

                    default:
                        if ($this->logger) {
                            $this->logger->error("Unknown API ResponseException. Will retry.");
                            $this->logger->error($e);
                        }

                        return $this->retry($request, $retry_attempt, $e);
                }
            }

        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error("Unknown API request error. Will retry.");
                $this->logger->error($e);
            }

            return $this->retry($request, $retry_attempt, $e);
        }

        return null;
    }

    /**
     * Retry api request on error response
     *
     * @param ClientHelper\ClientHelperInterface $request
     * @param int                                $retry_attempt
     * @param Exception                          $exception
     * @param int                                $timeout
     *
     * @return \stdClass
     *
     * @throws Exception
     * @throws RetryAfterException
     */
    private function retry(ClientHelper\ClientHelperInterface $request, $retry_attempt, Exception $exception, $timeout = 0)
    {
        // Retry attempt timeouts (in seconds)
        $retry_timeouts = array(2, 5, 10, 30);

        if ($this->was_request && $retry_attempt++ < 10) {
            if ($timeout < 1) {
                $timeout = isset($retry_timeouts[$retry_attempt]) ? $retry_timeouts[$retry_attempt] : 60;
            }
            if ($this->logger) {
                $this->logger->error(sprintf(
                    "Retry api request, attempt = %d, timeout = %d.",
                    $retry_attempt, $timeout
                ));
            }

            sleep($timeout);

            return $this->doRequest($request, $retry_attempt);
        }

        throw $exception;
    }
}
