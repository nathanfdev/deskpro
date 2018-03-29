<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\Compiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\PhpTermCompilerFactory;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\PhpAgentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\VisitorInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TicketChecker\Compiler\PhpTicketCheckerCompiler
 */
class PhpTicketCheckerCompilerSpec extends ObjectBehavior
{
    public function let(
        TermInterface $term,
        PhpTermCompilerFactory $factory,
        PhpAgentTermCompiler $term_compiler,
        PhpCheck $php_check,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($factory, [], $logger);
        $factory->getCompiler($term)->willReturn($term_compiler);
        $php_check->getVariables()->willReturn([]);
        $term_compiler->compile($term)->willReturn($php_check);
        $php_check->__toString()->willReturn('');
    }

    public function it_is_a_php_compiler(
        PhpCheck $php_check
    ) {
        $this->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\Compiler\PhpCompiler');
        $this->enginePostCompile($php_check);
    }

    public function it_will_pass_the_term_to_all_visitors(
        TermInterface $term,
        PhpTermCompilerFactory $factory,
        VisitorInterface $visitor1,
        VisitorInterface $visitor2,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($factory, [$visitor1, $visitor2], $logger);

        $visitor1->visit($term)->shouldBeCalled();
        $visitor2->visit($term)->shouldBeCalled();

        $this->compile($term);
    }

    public function it_will_make_a_php_check_from_a_non_composite_term_and_create_a_working_php_class(
        TermInterface $term,
        PhpTermCompilerFactory $factory,
        PhpAgentTermCompiler $term_compiler,
        PhpCheck $php_check
    ) {
        $factory->getCompiler($term)->willReturn($term_compiler);
        $term_compiler->compile($term)->willReturn($php_check);

        $this->compile($term)->shouldReturnAnInstanceOf(
            'DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck'
        );
    }
}
