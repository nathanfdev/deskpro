<?php

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
