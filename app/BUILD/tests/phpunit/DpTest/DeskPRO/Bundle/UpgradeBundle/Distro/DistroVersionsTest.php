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

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\UpgradeBundle\Distro;

use DeskPRO\Bundle\UpgradeBundle\Distro\DistroVersions;
use DeskPRO\Bundle\UpgradeBundle\Distro\Manifest\DistroRelease;
use DeskPRO\Bundle\UpgradeBundle\Distro\Manifest\DistroReleaseDetail;
use DpTest\DeskProTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;

class DistroVersionsTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_returns_results()
    {
        $distroVersions = new DistroVersions($this->createClient());
        $releases       = $distroVersions->getReleases();

        $this->assertEquals(2, count($releases));

        $latest = $distroVersions->getLatestRelease();
        $this->assertInstanceOf(DistroRelease::class, $latest);
        $this->assertEquals('15742.0', $latest->getId());

        $this->assertInstanceOf('DateTime', $latest->getDate());
        $this->assertEquals('2016-06-10 11:40:00', $latest->getDate()->format('Y-m-d H:i:s'));

        $this->assertEquals('stable', $latest->getTrack());

        $this->assertEquals('https://deskpro.github.io/releases/stable/2016-06/15742.0/release.json', $latest->getInfoUrl());
    }

    /**
     * @test
     */
    public function it_returns_details()
    {
        $distroVersions = new DistroVersions($this->createClient());
        $detail         = $distroVersions->getReleaseDetails($distroVersions->getLatestRelease());

        $this->assertInstanceOf(DistroReleaseDetail::class, $detail);
        $this->assertEquals($distroVersions->getLatestRelease(), $detail->getRelease());
        $this->assertEquals('https://github.com/DeskPRO/deskpro.github.io/blob/master/releases/stable/2016-06/15742.0/deskpro.zip?raw=true', $detail->getZipUrl());
        $this->assertEquals('05c5cc03182ff6a89c847173a3e538c38d939ad73208a7414ef30e196e4771cd', $detail->getSha256());
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_should_throw_exception_on_invalid_manifest()
    {
        $client = \Mockery::mock(ClientInterface::class);

        $manifestResponse = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', DistroVersions::VERSION_API_ROOT)->andReturn($manifestResponse);
        $manifestResponse->shouldReceive('getBody')->andReturn(Psr7\stream_for(json_encode(['foo' => 'bar'])));

        $distroVersions = new DistroVersions($client);
        $distroVersions->getReleases();
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_should_throw_exception_on_bogus_manifest()
    {
        $client = \Mockery::mock(ClientInterface::class);

        $manifestResponse = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', DistroVersions::VERSION_API_ROOT)->andReturn($manifestResponse);
        $manifestResponse->shouldReceive('getBody')->andReturn(Psr7\stream_for('This isnt JSON at all'));

        $distroVersions = new DistroVersions($client);
        $distroVersions->getReleases();
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_should_throw_exception_on_invalid_detail()
    {
        $client = \Mockery::mock(ClientInterface::class);

        $detailResponse = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', 'https://deskpro.github.io/releases/stable/2016-06/15742.0/release.json')->andReturn($detailResponse);
        $detailResponse->shouldReceive('getBody')->andReturn(Psr7\stream_for(json_encode(['foo' => 'bar'])));

        $distroVersions = new DistroVersions($client);
        $distroVersions->getReleases();
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_should_throw_exception_on_bogus_detail()
    {
        $client = \Mockery::mock(ClientInterface::class);

        $detailResponse = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', 'https://deskpro.github.io/releases/stable/2016-06/15742.0/release.json')->andReturn($detailResponse);
        $detailResponse->shouldReceive('getBody')->andReturn(Psr7\stream_for('This isnt JSON at all'));

        $distroVersions = new DistroVersions($client);
        $distroVersions->getReleases();
    }

    /**
     * @return MockInterface
     */
    private function createClient()
    {
        $manifestJson = <<<'JSON'
{
    "releases": [
        {
            "id": "15741.0",
            "date": "2016-06-09 11:40:00",
            "track": "stable",
            "info_url": "https://deskpro.github.io/releases/stable/2016-06/15741.0/release.json"
        },
        {
            "id": "15742.0",
            "date": "2016-06-10 11:40:00",
            "track": "stable",
            "info_url": "https://deskpro.github.io/releases/stable/2016-06/15742.0/release.json"
        }
    ]
}
JSON;

        $detailJson = <<<'JSON'
{
    "id": "15742.0",
    "date": "2016-06-10 11:40:00",
    "track": "stable",
    "commit": "67f6ad54857b5190c5e5acc96a4ba9aabb807cee",
    "zip_url": "https://github.com/DeskPRO/deskpro.github.io/blob/master/releases/stable/2016-06/15742.0/deskpro.zip?raw=true",
    "checksums": {
        "crc32": "b66232ec",
        "md5": "7b3bf315bb82edfc252cbaf1fe373a13",
        "sha1": "cf8b35352e0cbdac0f12e7f44d2eb18b6bab19e1",
        "sha256": "05c5cc03182ff6a89c847173a3e538c38d939ad73208a7414ef30e196e4771cd"
    }
}
JSON;

        $client = \Mockery::mock(ClientInterface::class);

        $manifestResponse = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', DistroVersions::MANIFEST_ENDPOINT)->andReturn($manifestResponse);
        $manifestResponse->shouldReceive('getBody')->andReturn(Psr7\stream_for($manifestJson));

        $detailResponse = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', 'https://deskpro.github.io/releases/stable/2016-06/15742.0/release.json')->andReturn($detailResponse);
        $detailResponse->shouldReceive('getBody')->andReturn(Psr7\stream_for($detailJson));

        return $client;
    }
}
