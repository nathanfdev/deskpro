<?php

namespace spec\DeskPRO\Bundle\AppBundle\Helper;

use PhpSpec\ObjectBehavior;

class UrlHostCheckerSpec extends ObjectBehavior
{
    public function it_fails_if_port_does_not_match()
    {
        $this->isMatch('http://site.com:80', 'site.com', '81')->shouldReturn(false);
        $this->isMatch('http://site.com:80', 'site.com', '443', false)->shouldReturn(false);
        $this->isMatch('http://site.com:80', 'site.com', '443', true)->shouldReturn(true);
    }

    public function it_defaults_to_port_80()
    {
        $this->isMatch('http://site.com', 'site.com', '80')->shouldReturn(true);
        $this->isMatch('http://site.com', 'site.com', '81')->shouldReturn(false);
        $this->isMatch('http://site.com', 'site.com', '443', false)->shouldReturn(false);
        $this->isMatch('http://site.com', 'site.com', '443', true)->shouldReturn(true);
    }

    public function it_requires_a_scheme_for_urls()
    {
        $this->isMatch('site.com', 'site.com', '80')->shouldReturn(false);
    }

    public function it_allows_absolute_uris()
    {
        $this->isMatch('/news', 'site.com', '80')->shouldReturn(true);
        $this->isMatch('/news', 'site.com:9000', '9000')->shouldReturn(true);
    }

    public function it_wont_allow_schemeless_urls()
    {
        $this->isMatch('//site.com', 'site.com', '80')->shouldReturn(false);
        $this->isMatch('//news.com', 'site.com', '80')->shouldReturn(false);
    }

    public function it_ensures_host_is_the_same()
    {
        $this->isMatch('http://site.com', 'site-b.com', '80')->shouldReturn(false);
        $this->isMatch('http://site.com', 'localhost', '80')->shouldReturn(false);

        $this->isMatch('http://site.com', 'site.com', '80')->shouldReturn(true);
        $this->isMatch('http://site.com:81', 'site.com', '81')->shouldReturn(true);
    }

    public function it_has_another_method_to_breakdown_the_request_url_for_you()
    {
        $this->isMatchUrl('http://site.com', 'http://google.com')->shouldReturn(false);
        $this->isMatchUrl('site.com', 'http://site.com:443')->shouldReturn(false);

        $this->isMatchUrl('http://site.com:443', 'http://site.com:443')->shouldReturn(true);
        $this->isMatchUrl('http://samesite.com/fooA', 'http://samesite.com/')->shouldReturn(true);
        $this->isMatchUrl('https://samesite.com/fooB', 'https://samesite.com/')->shouldReturn(true);
        $this->isMatchUrl('https://samesite.com:8043/fooX', 'https://samesite.com:8043/')->shouldReturn(true);
        $this->isMatchUrl('https://samesite.com:8043/fooD', 'https://samesite.com/')->shouldReturn(false);
        $this->isMatchUrl('http://samesite.com/fooE', 'https://samesite.com/', false)->shouldReturn(false);
        $this->isMatchUrl('http://samesite.com/fooE', 'https://samesite.com/', true)->shouldReturn(true);
        $this->isMatchUrl('http://site.com:443/fooF', 'http://site.com:443/')->shouldReturn(true);
        $this->isMatchUrl('/news', 'http://site.com:443')->shouldReturn(true);
    }

    public function it_simplify_urls()
    {
        $this->simplifyUrl('http://mydomain.com')->shouldBe('mydomain.com');
        $this->simplifyUrl('http://mydomain.com/en')->shouldBe('mydomain.com');
        $this->simplifyUrl('foo.com')->shouldBe('foo.com');
        $this->simplifyUrl('//foo.com')->shouldBe('foo.com');
        $this->simplifyUrl('http://mydomain.com:9000')->shouldBe('mydomain.com:9000');
        $this->simplifyUrl('https://mydomain.com')->shouldBe('mydomain.com');
        $this->simplifyUrl('https://samesite.com:8043/fooD')->shouldBe('samesite.com:8043');
        $this->simplifyUrl('//foo.com', true)->shouldBe('http://foo.com');
        $this->simplifyUrl('http://foo.com', true)->shouldBe('http://foo.com');
        $this->simplifyUrl('https://foo.com', true)->shouldBe('https://foo.com');
        $this->simplifyUrl('https://foo.com:8888', true)->shouldBe('https://foo.com:8888');
        $this->simplifyUrl('', true)->shouldBe('');
    }
}
