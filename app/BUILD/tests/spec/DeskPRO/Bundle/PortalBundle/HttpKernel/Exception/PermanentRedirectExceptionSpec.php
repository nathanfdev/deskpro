<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\PortalBundle\HttpKernel\Exception;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @mixin \DeskPRO\Bundle\PortalBundle\HttpKernel\Exception\PermanentRedirectException
 */
class PermanentRedirectExceptionSpec extends ObjectBehavior
{
    public function it_knows_route_details()
    {
        $this->beConstructedWith('list_articles', ['route' => 'params']);

        $this->getRouteName()->shouldBe('list_articles');
        $this->getRouteParams()->shouldBe(['route' => 'params']);
        $this->getUrlType()->shouldBe(UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}
