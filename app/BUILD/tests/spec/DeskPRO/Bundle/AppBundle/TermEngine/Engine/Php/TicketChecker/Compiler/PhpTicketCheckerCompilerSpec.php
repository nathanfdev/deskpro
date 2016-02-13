<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
        $this->beConstructedWith($factory, array(), $logger);
        $factory->getCompiler($term)->willReturn($term_compiler);
        $php_check->getVariables()->willReturn(array());
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
        $this->beConstructedWith($factory, array($visitor1, $visitor2), $logger);

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
