<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngineEvent
 */
class DbalEngineEventSpec extends ObjectBehavior
{
    public function it_has_a_query_and_a_context(
        DbalQuery $query,
        TermEngineContext $context
    ) {
        $this->beConstructedWith($query, $context);
        $this->getQuery()->shouldBe($query);
        $this->getContext()->shouldBe($context);
    }
}
