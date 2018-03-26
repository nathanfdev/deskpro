<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\UpdateBundle\Distro;

use DeskPRO\Bundle\UpdateBundle\Distro\DistroManifestLoader;
use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroRelease;
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
        $distroVersions = new DistroManifestLoader($this->createClient());
        $releases       = $distroVersions->loadReleases();

        $this->assertEquals(2, count($releases));

        $latest = $releases->getLatest();
        $this->assertInstanceOf(DistroRelease::class, $latest);
        $this->assertEquals('15742.0', $latest->getId());
        $this->assertEquals(165581571, $latest->getFilesize());

        $this->assertInstanceOf('DateTime', $latest->getDate());
        $this->assertEquals('2016-06-10 11:40:00', $latest->getDate()->format('Y-m-d H:i:s'));

        $this->assertEquals('https://deskpro.github.io/releases/stable/2016-06/15742.0/release.json', $latest->getDetailUrl());
        $this->assertEquals('https://github.com/DeskPRO/deskpro.github.io/blob/master/releases/stable/2016-06/15742.0/deskpro.zip?raw=true', $latest->getZipUrl());
    }

    /**
     * @test
     */
    public function it_filters_flags()
    {
        $distroVersions = new DistroManifestLoader($this->createClient());
        $releases1      = $distroVersions->loadReleases();

        $distroVersions = new DistroManifestLoader($this->createClient());
        $releases2      = $distroVersions->loadReleases(['with_flags' => 'auto_enabled']);

        $distroVersions = new DistroManifestLoader($this->createClient());
        $releases3      = $distroVersions->loadReleases(['with_flags' => 'foo']);

        $distroVersions = new DistroManifestLoader($this->createClient());
        $releases4      = $distroVersions->loadReleases(['with_flags' => ['foo', 'bar']]);

        $this->assertEquals(2, $releases1->count());
        $this->assertEquals(1, $releases2->count());
        $this->assertEquals(2, $releases3->count());
        $this->assertEquals(2, $releases4->count());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function it_should_throw_exception_on_invalid_manifest()
    {
        $client = \Mockery::mock(ClientInterface::class);

        $manifestResponse = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', DistroManifestLoader::VERSION_API_ROOT)->andReturn($manifestResponse);
        $manifestResponse->shouldReceive('getBody')->andReturn(Psr7\stream_for(json_encode(['foo' => 'bar'])));

        $distroVersions = new DistroManifestLoader($client);
        $distroVersions->loadReleases();
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function it_should_throw_exception_on_bogus_manifest()
    {
        $client = \Mockery::mock(ClientInterface::class);

        $manifestResponse = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', DistroManifestLoader::VERSION_API_ROOT)->andReturn($manifestResponse);
        $manifestResponse->shouldReceive('getBody')->andReturn(Psr7\stream_for('This isnt JSON at all'));

        $distroVersions = new DistroManifestLoader($client);
        $distroVersions->loadReleases();
    }

    /**
     * @return MockInterface
     */
    private function createClient()
    {
        $manifestJson = file_get_contents(__DIR__.'/../data/example_manifest.json');

        $client = \Mockery::mock(ClientInterface::class);

        $manifestResponse = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', DistroManifestLoader::MANIFEST_ENDPOINT)->andReturn($manifestResponse);
        $manifestResponse->shouldReceive('getBody')->andReturn(Psr7\stream_for($manifestJson));

        return $client;
    }
}
