<?php

namespace spec\Application\AppBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SecurityHeadersResponseListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Application\AppBundle\EventListener\SecurityHeadersResponseListener');
    }
}
