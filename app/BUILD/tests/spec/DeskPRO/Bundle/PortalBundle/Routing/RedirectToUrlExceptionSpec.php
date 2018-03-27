<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\Routing;

use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\Routing\RedirectToUrlException
 */
class RedirectToUrlExceptionSpec extends ObjectBehavior
{
    public function it_has_the_url_to_redirect_to()
    {
        $this->beConstructedWith('http://google.com');

        $this->getUrl()->shouldBe('http://google.com');
    }
}
