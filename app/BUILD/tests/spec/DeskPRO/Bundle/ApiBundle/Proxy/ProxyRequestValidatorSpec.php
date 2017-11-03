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

namespace spec\DeskPRO\Bundle\ApiBundle\Proxy;

use DeskPRO\Bundle\ApiBundle\Proxy\ApplicationProxyRequest;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ProxyRequestValidatorSpec.
 *
 * @mixin \DeskPRO\Bundle\ApiBundle\Proxy\ProxyRequestValidator
 */
class ProxyRequestValidatorSpec extends ObjectBehavior
{
    public function let(ValidatorInterface $validator)
    {
        $this->beConstructedWith($validator);
    }

    public function it_throws_exception_on_empty_proxy_url(ApplicationProxyRequest $request)
    {
        $this->shouldThrow(new \RuntimeException('No proxy url provided.'))->during('validateWhitelistableRequest', [$request]);
    }

    public function it_throws_exception_on_url_validation(ApplicationProxyRequest $request, ValidatorInterface $validator)
    {
        $request->getProxyUrl()->willReturn('http://deskpro-dev/api/some-endpoint');
        $validator->validate('http://deskpro-dev/api/some-endpoint', Argument::type('array'))->willReturn(false);

        $this->shouldThrow(new \RuntimeException('The proxy url is not valid.'))->during('validateWhitelistableRequest', [$request]);
    }

    public function it_throws_exception_if_empty_white_list(ApplicationProxyRequest $request)
    {
        $request->getProxyUrl()->willReturn('http://deskpro-dev/api/some-endpoint');
        $request->getWhiteList()->willReturn([]);

        $this->shouldThrow(new \RuntimeException('No proxy whitelist is defined.'))->during('validateWhitelistableRequest', [$request]);
    }

    public function it_throws_exception_on_exact_match(ApplicationProxyRequest $request)
    {
        $request->getProxyUrl()->willReturn('http://deskpro-dev/api/some-endpoint');
        $request->getWhiteList()->willReturn([
            'http://my-site/api/some-endpoint',
        ]);

        $this->shouldThrow(new \RuntimeException('The proxy url is not allowed (allowed http://my-site/api/some-endpoint).'))->during('validateWhitelistableRequest', [$request]);
    }

    public function it_should_pass_on_exact_match(ApplicationProxyRequest $request)
    {
        $request->getProxyUrl()->willReturn('http://deskpro-dev/api/some-endpoint');
        $request->getWhiteList()->willReturn([
            'http://deskpro-dev/api/some-endpoint',
        ]);

        $this->validateWhitelistableRequest($request);
    }

    public function it_should_pass_on_exact_match_with_query_params(ApplicationProxyRequest $request)
    {
        $request->getProxyUrl()->willReturn('http://deskpro-dev/api/some-endpoint?param=value');
        $request->getWhiteList()->willReturn([
            'http://deskpro-dev/api/some-endpoint',
        ]);

        $this->validateWhitelistableRequest($request);
    }

    public function it_should_pass_on_exact_match_with_custom_port(ApplicationProxyRequest $request)
    {
        $request->getProxyUrl()->willReturn('http://deskpro-dev:8888/api/some-endpoint');
        $request->getWhiteList()->willReturn([
            'http://deskpro-dev:8888/api/some-endpoint',
        ]);

        $this->validateWhitelistableRequest($request);
    }

    public function it_should_pass_on_partial_match(ApplicationProxyRequest $request)
    {
        $request->getProxyUrl()->willReturn('http://deskpro-dev/api/some-endpoint');
        $request->getWhiteList()->willReturn([
            'http://deskpro-dev/api/*',
        ]);

        $this->validateWhitelistableRequest($request);
    }

    public function it_should_pass_on_regex_match(ApplicationProxyRequest $request)
    {
        $request->getProxyUrl()->willReturn('http://deskpro-dev/api/some-endpoint');
        $request->getWhiteList()->willReturn([
            '/deskpro-dev/api/',
        ]);

        $this->validateWhitelistableRequest($request);
    }
}
