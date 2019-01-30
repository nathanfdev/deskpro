<?php

namespace Application\EmailBundle\SourceMapper\PendingQueuer;

use Aws\Credentials\CredentialProvider;
use Aws\Sqs\SqsClient;

class CloudEmailPendingQueuer implements PendingQueuerInterface
{
    /** @var SqsClient */
    private $client;

    /** @var string */
    private $queueUrl;

    /** @var string */
    private $apiKey;

    /**
     * @param string $region
     * @return SqsClient
     */
    static public function createSqsClient($region)
    {
        $provider = CredentialProvider::defaultProvider();
        return new SqsClient([
            'version' => '2012-11-05',
            'credentials' => $provider,
            'region'  => $region // TO DO THIS NEEDS TO COME FROM CONFIGURATION
        ]);
    }

    /**
     * @param string $queueUrl the outgoing queue url, in this format https://sqs.<region>.amazonaws.com/<aws_account_id>/<queue_name>
     * @param string $deskproAPIKey
     * @return CloudEmailPendingQueuer
     * @throws \Exception
     */
    static public function create($queueUrl, $deskproAPIKey)
    {
        if (empty($deskproAPIKey)) {
            throw new \Exception("the deskpro api key can not be empty");
        }

        $region = null;
        $urlComponents = parse_url($queueUrl, PHP_URL_HOST);
        if (!empty($urlComponents) && preg_match('/sqs\.([^.]+).+/',$urlComponents, $matches)) {
            $region = $matches[1];
        }

        if(empty($region)) {
            throw new \Exception(
            "could not resolve the region from the queue url. ".
            'Make sure the url is in the format https://sqs.<region>.amazonaws.com/<aws_account_id>/<queue_name>'
            );
        }

        $client = CloudEmailPendingQueuer::createSqsClient($region);
        return new CloudEmailPendingQueuer($client, $queueUrl, $deskproAPIKey);
    }

    /**
     * CloudEmailPendingQueuer constructor.
     * @param SqsClient $client
     * @param string $queueUrl
     * @param string $apiKey
     */
    public function __construct(SqsClient $client, $queueUrl, $apiKey)
    {
        $this->client = $client;
        $this->queueUrl = $queueUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * Adds a message to an external queue service.
     *
     * @param array $source
     */
    public function queueMessageSource( array $source )
    {
        //TODO find out where to get the build id

        $this->client->sendMessage([
            'DelaySeconds' => 0,
//            'MessageAttributes' => [
//                // ...
//            ],
            'MessageBody' => json_encode([
                "id" => DPC_SITE_ID,
                 "masterDomain" => DPC_SITE_DOMAIN,
                 "buildId" => "0.0.0",
                 "apiKey" => $this->apiKey,
                 "secret" => $this->apiKey
            ]),
            'MessageDeduplicationId' => DPC_SITE_ID,
            'MessageGroupId' => 'sites',
            'QueueUrl' => $this->queueUrl, // REQUIRED
        ]);
    }



}
