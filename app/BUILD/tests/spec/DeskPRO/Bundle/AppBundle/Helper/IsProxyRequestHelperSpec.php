<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Helper;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Helper\IsProxyRequestHelper
 */
class IsProxyRequestHelperSpec extends ObjectBehavior
{
    public function it_tells_you_if_the_request_object_is_a_proxy_request(
        Request $proxy_request,
        Request $not_proxy_request
    ) {
        $proxy_request->getPathInfo()->willReturn('/_proxy?params=foo');
        $not_proxy_request->getPathInfo()->willReturn('/articles');

        $this->check($proxy_request)->shouldBe(true);
        $this->check($not_proxy_request)->shouldBe(false);
    }
}
