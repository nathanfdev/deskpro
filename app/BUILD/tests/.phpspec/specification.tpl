<?php

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace %namespace%;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use %subject%;

/**
 * @mixin \%subject%
 */
class %name% extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('%subject%');
    }
}
