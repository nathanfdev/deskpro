<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\DbalAgentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\PhpAgentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\PhpTicketStatusTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\PhpTermCompilerFactory
 */
class PhpTermCompilerFactorySpec extends ObjectBehavior
{
    public function let(
        PhpAgentTermCompiler $agent_compiler,
        PhpTicketStatusTermCompiler $status_compiler
    ) {
        $this->beConstructedWith(
            [
                'DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm'               => $agent_compiler,
                'DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm' => $status_compiler,
            ]
        );
    }

    public function it_finds_the_right_compiler(
        DbalAgentTermCompiler $agent_compiler,
        PhpTicketStatusTermCompiler $status_compiler
    ) {
        $this->getCompiler(new AgentTerm())->shouldBe($agent_compiler);
        $this->getCompiler(new TicketStatusTerm())->shouldBe($status_compiler);
    }

    public function it_throws_an_exception_if_no_compiler_found(
        DbalAgentTermCompiler $agent_compiler,
        PhpTicketStatusTermCompiler $status_compiler
    ) {
        $this->shouldThrow('\InvalidArgumentException')->during('getCompiler', [new FakeTerm()]);
    }
}

class FakeTerm extends AbstractTerm
{
    public static function configureOptions(TermOptionsResolver $resolver)
    {
    }

    /**
     * Returns an array of all possible OP_ codes that this term supports.
     *
     * @return array
     */
    public function getSupportedOps()
    {
        // TODO: Implement getSupportedOps() method.
    }

    /**
     * Gets the default OP_ code for this term.
     *
     * Immediately after instantiating the term, getOp() should return this value
     * unless a constructor argument exists that allows an override.
     *
     * This OP must be a supported OP in getSupportedOps()
     *
     * @return string
     */
    public function getDefaultOp()
    {
        // TODO: Implement getDefaultOp() method.
    }
}
