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

namespace spec\DeskPRO\Bundle\AppBundle\Helper;

use PhpSpec\ObjectBehavior;

class UrlHostCheckerSpec extends ObjectBehavior
{
    public function it_fails_if_port_does_not_match()
    {
        $this->isMatch('http://site.com:80', 'site.com', '81')->shouldReturn(false);
        $this->isMatch('http://site.com:80', 'site.com', '443')->shouldReturn(false);
    }

    public function it_defaults_to_port_80()
    {
        $this->isMatch('http://site.com', 'site.com', '80')->shouldReturn(true);
        $this->isMatch('http://site.com', 'site.com', '81')->shouldReturn(false);
        $this->isMatch('http://site.com', 'site.com', '443')->shouldReturn(false);
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
        $this->isMatchUrl('http://samesite.com/', 'http://samesite.com/')->shouldReturn(true);
        $this->isMatchUrl('https://samesite.com/', 'https://samesite.com/')->shouldReturn(true);
        $this->isMatchUrl('https://samesite.com:8043/', 'https://samesite.com:8043/')->shouldReturn(true);
        $this->isMatchUrl('https://samesite.com:8043/', 'https://samesite.com/')->shouldReturn(false);
        $this->isMatchUrl('http://samesite.com/', 'https://samesite.com/')->shouldReturn(false);
        $this->isMatchUrl('http://site.com:443/', 'http://site.com:443/')->shouldReturn(true);
        $this->isMatchUrl('/news', 'http://site.com:443')->shouldReturn(true);
    }
}
