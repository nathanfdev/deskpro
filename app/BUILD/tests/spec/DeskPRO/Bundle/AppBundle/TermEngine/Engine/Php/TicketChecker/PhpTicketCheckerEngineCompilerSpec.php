<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheckCacher;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\Compiler\PhpTicketCheckerCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\PhpTicketCheckerEngineCompiler
 */
class PhpTicketCheckerEngineCompilerSpec extends ObjectBehavior
{
    public function let(
        PhpTicketCheckerCompiler $compiler,
        PhpCheckCacher $cache,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($compiler, $cache, $logger);
    }

    public function it_compiles_a_filter_if_it_is_not_cached(
        TicketFilter $filter,
        \DateTime $filter_updated,
        TermInterface $filter_term,
        PhpCheck $php_check,
        PhpTicketCheckerCompiler $compiler,
        PhpCheckCacher $cache
    ) {
        $filter->getId()->willReturn(3);
        $filter->getTitle()->willReturn('title');
        $filter->getDateUpdated()->willReturn($filter_updated);
        $filter->getTerm()->willReturn($filter_term);
        $filter_updated->getTimestamp()->willReturn(1234567890);

        // no cached
        $cache->fetchCheck('3.1234567890')->willReturn(null);

        $cache->saveCheck('3.1234567890', $php_check)->shouldBeCalled();

        $compiler->compile($filter_term)->willReturn($php_check);

        $this->compile($filter)->shouldReturn($php_check);
    }

    public function it_returns_the_cached_version_of_the_compiled_query_if_exists(
        TicketFilter $filter,
        \DateTime $filter_updated,
        TermInterface $filter_term,
        PhpCheck $php_check,
        PhpTicketCheckerCompiler $compiler,
        PhpCheckCacher $cache
    ) {
        $filter->getId()->willReturn(3);
        $filter->getTitle()->willReturn('title');
        $filter->getDateUpdated()->willReturn($filter_updated);
        $filter_updated->getTimestamp()->willReturn(123456789011);

        // found it! cached version
        $cache->fetchCheck('3.123456789011')->willReturn($php_check);

        // so it will not compile
        $compiler->compile($filter_term)->shouldNotBeCalled();

        $this->compile($filter)->shouldReturn($php_check);
    }
}
