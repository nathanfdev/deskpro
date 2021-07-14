<?php

namespace Application\DeskPRO\BlobStorage\StorageAdapter\Client;

use Application\DeskPRO\Proxy\OutboundHttpProxy;
use Aws\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

/**
 * Class S3Client
 *
 * S3 Client to be used in cloud. Requests an authorised and re-signed URL for PUT requests.
 * Note: any of the AWS SDK command helper methods may be extended in this way.
 *
 * @package Application\DeskPRO\BlobStorage\StorageAdapter\Client
 */
class S3Client extends \Aws\S3\S3Client
{
    /**
     * Stale headers contain either the old request signature or headers that may conflict
     * with the newly resigned URL
     */
    const STALE_HEADERS = [
        'Authorization',
        'x-amz-date',
        'x-amz-acl',
        'aws-sdk-invocation-id',
        'aws-sdk-retry',
        'x-amz-content-sha256',
        'X-Amz-Date',
    ];

    /**
     * @var OutboundHttpProxy
     */
    private $outboundHttpProxy;

    /**
     * S3Client constructor.
     *
     * @param array $args
     * @param OutboundHttpProxy $outboundHttpProxy
     */
    public function __construct(array $args, OutboundHttpProxy $outboundHttpProxy)
    {
        parent::__construct($args);
        $this->outboundHttpProxy = $outboundHttpProxy;
    }

    /**
     * Overloaded PutObject command so that request is resigned
     *
     * @param array $args
     * @return \Aws\Result
     */
    public function putObject(array $args)
    {
        return $this->reSignRequest('PutObject', 'PUT', $args);
    }

    /**
     * Re-sign an arbitrary AWS command
     *
     * @param string $commandName
     * @param string $method
     * @param array $args
     * @return mixed
     */
    protected function reSignRequest($commandName, $method, array $args)
    {
        $command = $this->getCommand($commandName, $args);
        $list = $command->getHandlerList();

        $region = $this->getConfig('signing_region');

        // Append to the signing middleware as we need a fully signed request so we can
        // replace it as the final step
        $list->appendSign(
            Middleware::mapRequest(function (RequestInterface $request) use ($args, $region, $method) {
                $serviceToken = $this
                    ->outboundHttpProxy
                    ->getS3ServiceToken(
                        DPC_SITE_ID,
                        $region,
                        $args['Bucket'],
                        $args['Key'],
                        $method,
                        $request->getHeader('x-amz-acl')[0]
                    )
                ;

                $reSignedUrl = $this
                    ->outboundHttpProxy
                    ->getPreSignedS3Url($serviceToken)
                ;

                $request->withUri(new Uri($reSignedUrl));

                foreach (self::STALE_HEADERS as $staleHeader) {
                    $request->withoutHeader($staleHeader);
                }

                return $request;
            }),
            're-sign-'.strtolower($commandName)
        );

        return $this->execute($command);
    }
}
