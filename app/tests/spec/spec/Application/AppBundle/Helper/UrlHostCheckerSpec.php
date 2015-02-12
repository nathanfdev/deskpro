<?php

namespace spec\Application\AppBundle\Helper;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UrlHostCheckerSpec extends ObjectBehavior
{
    function it_fails_if_port_does_not_match()
    {
        $this->isMatch('http://site.com:80', 'site.com', '81')->shouldReturn(false);
        $this->isMatch('http://site.com:80', 'site.com', '443')->shouldReturn(false);
    }

    function it_defaults_to_port_80()
    {
        $this->isMatch('http://site.com', 'site.com', '80')->shouldReturn(true);
        $this->isMatch('http://site.com', 'site.com', '81')->shouldReturn(false);
        $this->isMatch('http://site.com', 'site.com', '443')->shouldReturn(false);
    }

    function it_requires_a_scheme_for_urls()
    {
        $this->isMatch('site.com', 'site.com', '80')->shouldReturn(false);
    }

    function it_allows_absolute_uris()
    {
        $this->isMatch('/news', 'site.com', '80')->shouldReturn(true);
        $this->isMatch('/news', 'site.com:9000', '9000')->shouldReturn(true);
    }

    function it_wont_allow_schemeless_urls()
    {
        $this->isMatch('//site.com', 'site.com', '80')->shouldReturn(false);
        $this->isMatch('//news.com', 'site.com', '80')->shouldReturn(false);
    }

    function it_ensures_host_is_the_same()
    {
        $this->isMatch('http://site.com', 'site-b.com', '80')->shouldReturn(false);
        $this->isMatch('http://site.com', 'localhost', '80')->shouldReturn(false);

        $this->isMatch('http://site.com', 'site.com', '80')->shouldReturn(true);
        $this->isMatch('http://site.com:81', 'site.com', '81')->shouldReturn(true);
    }

    function it_has_another_method_to_breakdown_the_request_url_for_you()
    {
        $this->isMatchUrl('http://site.com', 'http://google.com')->shouldReturn(false);
        $this->isMatchUrl('site.com', 'http://site.com:443')->shouldReturn(false);

        $this->isMatchUrl('http://site.com:443', 'http://site.com:443')->shouldReturn(true);
        $this->isMatchUrl('/news', 'http://site.com:443')->shouldReturn(true);
    }
}
