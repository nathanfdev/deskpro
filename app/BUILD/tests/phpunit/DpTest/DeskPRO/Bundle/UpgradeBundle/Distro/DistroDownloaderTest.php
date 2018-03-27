<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\UpdateBundle\Distro;

use DeskPRO\Bundle\UpdateBundle\Distro\DistroDownloader;
use DeskPRO\Bundle\UpdateBundle\Distro\Manifest\DistroRelease;
use DpTest\DeskProTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use org\bovigo\vfs\vfsStream;
use Psr\Http\Message\ResponseInterface;

class DistroDownloaderTest extends DeskProTestCase
{
    /**
     * @return string
     */
    private function getDistroZipPath()
    {
        return __DIR__.'/../data/distro_15742.0.zip';
    }

    /**
     * @test
     */
    public function it_downloads_zip_file()
    {
        $client   = \Mockery::mock(ClientInterface::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', 'https://example.com/foobar.zip')->andReturn($response);
        $response->shouldReceive('getBody')->andReturn(Psr7\stream_for(file_get_contents($this->getDistroZipPath())));

        $dl     = new DistroDownloader($client);
        $detail = \Mockery::mock(DistroRelease::class);
        $detail->shouldReceive('getId')->andReturn('123');
        $detail->shouldReceive('getZipUrl')->andReturn('https://example.com/foobar.zip');
        $detail->shouldReceive('getSha256')->andReturn(hash_file('sha256', $this->getDistroZipPath()));

        $target = vfsStream::setup('download_distro_zip');
        $dl->download($detail, $target->url().'/deskpro.zip');

        $this->assertTrue($target->hasChild('deskpro.zip'));
        $this->assertEquals(filesize($this->getDistroZipPath()), $target->getChild('deskpro.zip')->size());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function it_throws_exception_for_bad_checksum()
    {
        $client   = \Mockery::mock(ClientInterface::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', 'https://example.com/foobar.zip')->andReturn($response);
        $response->shouldReceive('getBody')->andReturn(Psr7\stream_for(file_get_contents($this->getDistroZipPath())));

        $dl     = new DistroDownloader($client);
        $detail = \Mockery::mock(DistroRelease::class);
        $detail->shouldReceive('getId')->andReturn('123');
        $detail->shouldReceive('getZipUrl')->andReturn('https://example.com/foobar.zip');
        $detail->shouldReceive('getSha256')->andReturn(hash('sha256', 'foo'));

        $target = vfsStream::setup('download_distro_zip');
        $dl->download($detail, $target->url().'/deskpro.zip');
    }
}
