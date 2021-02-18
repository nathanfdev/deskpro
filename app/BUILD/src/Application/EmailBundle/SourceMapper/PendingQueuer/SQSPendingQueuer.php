<?php

namespace Application\EmailBundle\SourceMapper\PendingQueuer;

use Aws\Credentials\CredentialProvider;
use Aws\Sqs\SqsClient;

class SQSPendingQueuer implements PendingQueuerInterface
{
    /** @var SqsClient */
    private $client;

    /**
     * @var array
     */
    private $data_items = [];

    /**
     * @var string
     */
    private $queueUrl;

    /**
     * @param string $queueUrl the outgoing queue url, in this format https://sqs.<region>.amazonaws.com/<aws_account_id>/<queue_name>
     *
     * @throws \Exception
     *
     * @return SQSPendingQueuer
     */
    public static function create($region, $queueUrl, $endpoint = null)
    {
        $provider = CredentialProvider::defaultProvider();
        $params   = [
            'version'     => '2012-11-05',
            'credentials' => $provider,
            'region'      => $region,
        ];
        if ($endpoint) {
            $params['endpoint'] = $endpoint;
        }
        $sqsClient = new SqsClient($params);

        return new self($sqsClient, $queueUrl);
    }

    /**
     * @param SqsClient $client
     */
    public function __construct(SqsClient $client, $queueUrl)
    {
        $this->client   = $client;
        $this->queueUrl = $queueUrl;

        $me = $this;

        $pushAll = function () use ($me) {
            try {
                $me->pushAll();
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        };

        register_shutdown_function($pushAll);

        // Register new dp_push_outgoing_sqs_emails tag
        \DpShutdown::add($pushAll, [], 'dp_push_outgoing_sqs_emails', -4096);

        // Run with the existing db_done_trans_commit tag
        \DpShutdown::add($pushAll, [], 'db_done_trans_commit', -4096);
    }

    /**
     * Pushes all pendning rows to the server.
     */
    public function pushAll()
    {
        // According to API doc
        // > sendMessageBatch Delivers up to ten messages to the specified queue
        $chunks = array_chunk($this->data_items, 10);
        $this->data_items = [];

        foreach ($chunks as $chunk) {
            $this->client->sendMessageBatch([
                'QueueUrl' => $this->queueUrl,
                'Entries'  => $chunk,
            ]);
        }
    }

    /**
     * @param array $source
     */
    public function queueMessageSource(array $source)
    {
        $data = [
            'uuid' => $source['uuid'],
        ];

        if (defined('DPC_IS_CLOUD')) {
            $data['dpc_site_id'] = DPC_SITE_ID;
        }

        $this->data_items[] = [
            'Id'          => $source['uuid'],
            'MessageBody' => json_encode($data),
        ];
    }
}
