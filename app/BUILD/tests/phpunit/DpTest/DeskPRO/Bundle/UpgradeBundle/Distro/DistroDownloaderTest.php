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
        $detail->shouldReceive('getZipUrl')->andReturn('https://example.com/foobar.zip');
        $detail->shouldReceive('getSha256')->andReturn(hash_file('sha256', $this->getDistroZipPath()));

        $target = vfsStream::setup('download_distro_zip');
        $dl->download($detail, $target->url().'/deskpro.zip');

        $this->assertTrue($target->hasChild('deskpro.zip'));
        $this->assertEquals(filesize($this->getDistroZipPath()), $target->getChild('deskpro.zip')->size());
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function it_throws_exception_for_bad_checksum()
    {
        $client   = \Mockery::mock(ClientInterface::class);
        $response = \Mockery::mock(ResponseInterface::class);
        $client->shouldReceive('request')->with('GET', 'https://example.com/foobar.zip')->andReturn($response);
        $response->shouldReceive('getBody')->andReturn(Psr7\stream_for(file_get_contents($this->getDistroZipPath())));

        $dl     = new DistroDownloader($client);
        $detail = \Mockery::mock(DistroRelease::class);
        $detail->shouldReceive('getZipUrl')->andReturn('https://example.com/foobar.zip');
        $detail->shouldReceive('getSha256')->andReturn(hash('sha256', 'foo'));

        $target = vfsStream::setup('download_distro_zip');
        $dl->download($detail, $target->url().'/deskpro.zip');
    }
}
