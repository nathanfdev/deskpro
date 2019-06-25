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
    public static function create($queueUrl)
    {
        $region        = null;
        $urlComponents = parse_url($queueUrl, PHP_URL_HOST);
        if (!empty($urlComponents) && preg_match('/sqs\.([^.]+).+/', $urlComponents, $matches)) {
            $region = $matches[1];
        }

        if (empty($region)) {
            throw new \Exception(
            'could not resolve the region from the queue url. '.
            'Make sure the url is in the format https://sqs.<region>.amazonaws.com/<aws_account_id>/<queue_name>'
            );
        }

        $provider  = CredentialProvider::defaultProvider();
        $sqsClient = new SqsClient([
            'version'     => '2012-11-05',
            'credentials' => $provider,
            'region'      => $region,
        ]);

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
        register_shutdown_function(function () use ($me) {
            try {
                $me->pushAll();
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        });
    }

    /**
     * Pushes all pendning rows to the server.
     */
    public function pushAll()
    {
        foreach ($this->data_items as $d) {
            $this->client->sendMessage([
                'DelaySeconds' => 0,
    //            'MessageAttributes' => [
    //                // ...
    //            ],
                'MessageBody'            => json_encode($d),
                'MessageDeduplicationId' => $d['dpc_site_id'],
                'MessageGroupId'         => 'sites',
                'QueueUrl'               => $this->queueUrl, // REQUIRED
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

        $this->data_items[] = $data;
    }
}
