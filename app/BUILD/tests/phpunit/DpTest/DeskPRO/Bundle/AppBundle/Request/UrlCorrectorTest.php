<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\Request\UrlCorrector;
use DpTest\DeskProTestCase;
use Symfony\Component\HttpFoundation\Request;

class UrlCorrectorTest extends DeskProTestCase
{
    /**
     * @param string $uri
     *
     * @return Request
     */
    private function createRequest($uri)
    {
        if (strpos($uri, 'sub-dir') !== false) {
            return Request::create($uri, 'GET', [], [], [], [
                'SCRIPT_NAME'     => '/sub-dir/index.php',
                'SCRIPT_FILENAME' => 'index.php',
            ]);
        } else {
            return Request::create($uri, 'GET', [], [], [], [
                'SCRIPT_NAME'     => 'index.php',
                'SCRIPT_FILENAME' => 'index.php',
            ]);
        }
    }

    //###################################################################################################################
    // Top-level
    //###################################################################################################################

    public function testCorrectIndex()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'http://foo.example.com/',
        ]);

        $request = $this->createRequest('http://foo.example.com/index.php/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_INDEX_SEGMENT], $urlCorrector->getCorrections($request));
        $this->assertEquals('http://foo.example.com/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testCorrectHost()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'http://foo.example.com/',
        ]);

        $request = $this->createRequest('http://bar.example.com/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_HOST], $urlCorrector->getCorrections($request));
        $this->assertEquals('http://foo.example.com/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testCorrectScheme()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'https://foo.example.com/',
        ]);

        $request = $this->createRequest('http://foo.example.com/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_HTTPS], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testNoCorrectScheme()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'http://foo.example.com/',
        ]);

        $request = $this->createRequest('https://foo.example.com/new-ticket?foo=bar');

        $this->assertEquals([], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testNoChange()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'https://foo.example.com/',
        ]);

        $request = $this->createRequest('https://foo.example.com/new-ticket?foo=bar');

        $this->assertEquals([], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testAllChange()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'https://foo.example.com/',
        ]);

        $request = $this->createRequest('http://bar.example.com/index.php/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_INDEX_SEGMENT, UrlCorrector::CORRECTION_HTTPS, UrlCorrector::CORRECTION_HOST], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    //###################################################################################################################
    // In a sub-dir
    //###################################################################################################################

    public function testCorrectIndexSubDir()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'http://foo.example.com/sub-dir/',
        ]);

        $request = $this->createRequest('http://foo.example.com/sub-dir/index.php/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_INDEX_SEGMENT], $urlCorrector->getCorrections($request));
        $this->assertEquals('http://foo.example.com/sub-dir/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testCorrectHostSubDir()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'http://foo.example.com/sub-dir/',
        ]);

        $request = $this->createRequest('http://bar.example.com/sub-dir/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_HOST], $urlCorrector->getCorrections($request));
        $this->assertEquals('http://foo.example.com/sub-dir/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testCorrectSchemeSubDir()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'https://foo.example.com/sub-dir/',
        ]);

        $request = $this->createRequest('http://foo.example.com/sub-dir/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_HTTPS], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com/sub-dir/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testNoCorrectSchemeSubDir()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'http://foo.example.com/sub-dir/',
        ]);

        $request = $this->createRequest('https://foo.example.com/sub-dir/new-ticket?foo=bar');

        $this->assertEquals([], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com/sub-dir/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testNoChangeSubDir()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'https://foo.example.com/sub-dir/',
        ]);

        $request = $this->createRequest('https://foo.example.com/sub-dir/new-ticket?foo=bar');

        $this->assertEquals([], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com/sub-dir/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testAllChangeSubDir()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'https://foo.example.com/sub-dir/',
        ]);

        $request = $this->createRequest('http://bar.example.com/sub-dir/index.php/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_INDEX_SEGMENT, UrlCorrector::CORRECTION_HTTPS, UrlCorrector::CORRECTION_HOST], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com/sub-dir/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testChangedPort()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'https://foo.example.com:8080/sub-dir/',
        ]);

        $request = $this->createRequest('https://foo.example.com:9000/sub-dir/index.php/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_INDEX_SEGMENT, UrlCorrector::CORRECTION_HOST], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com:8080/sub-dir/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testChangedPortScheme()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'https://foo.example.com:8080/sub-dir/',
        ]);

        $request = $this->createRequest('http://foo.example.com:9000/sub-dir/index.php/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_INDEX_SEGMENT, UrlCorrector::CORRECTION_HTTPS, UrlCorrector::CORRECTION_HOST], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com:8080/sub-dir/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }

    public function testChangedNoPort()
    {
        $urlCorrector = new UrlCorrector([
            'autoCorrectScheme' => true,
            'autoCorrectHost'   => true,
            'helpdeskUrl'       => 'https://foo.example.com:8080/sub-dir/',
        ]);

        $request = $this->createRequest('http://foo.example.com/sub-dir/index.php/new-ticket?foo=bar');

        $this->assertEquals([UrlCorrector::CORRECTION_INDEX_SEGMENT, UrlCorrector::CORRECTION_HTTPS, UrlCorrector::CORRECTION_HOST], $urlCorrector->getCorrections($request));
        $this->assertEquals('https://foo.example.com:8080/sub-dir/new-ticket?foo=bar', $urlCorrector->getCorrectedUrl($request));
    }
}
