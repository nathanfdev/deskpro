<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\UpgradeBundle\Distro;

use DeskPRO\Bundle\UpgradeBundle\Distro\Manifest\DistroReleaseDetail;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\RequestOptions;

class DistroDownloader
{
    /**
     * @var ClientInterface
     */
    private $client;

    public static function create()
    {
        $client = new HttpClient([
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::TIMEOUT         => 1200,
        ]);

        return new self($client);
    }

    /**
     * DistroDownloader constructor.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param DistroReleaseDetail $releaseDetail
     * @param string              $targetPath
     */
    public function download(DistroReleaseDetail $releaseDetail, $targetPath)
    {
        $targetFp     = Psr7\try_fopen($targetPath, 'w');
        $targetStream = Psr7\stream_for($targetFp);

        $request = $this->client->request('GET', $releaseDetail->getZipUrl());
        Psr7\copy_to_stream($request->getBody(), $targetStream);
        fclose($targetFp);

        $hash = hash_file('sha256', $targetPath);
        if ($releaseDetail->getSha256() !== $hash) {
            throw new \RuntimeException('The downloaded file has an invalid checksum.');
        }
    }
}
