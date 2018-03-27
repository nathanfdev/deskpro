<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalAgentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalCompositeTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalDepartmentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\DbalTermCompilerFactory
 */
class DbalTermCompilerFactorySpec extends ObjectBehavior
{
    public function let(
        DbalAgentTermCompiler $agent_compiler,
        DbalDepartmentTermCompiler $department_compiler,
        DbalCompositeTermCompiler $composite_compiler
    ) {
        $this->beConstructedWith(
            [
                'DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm'      => $agent_compiler,
                'DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm' => $department_compiler,
                'DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm'  => $composite_compiler,
            ]
        );
    }

    public function it_finds_the_right_compiler(
        DbalAgentTermCompiler $agent_compiler,
        DbalDepartmentTermCompiler $department_compiler,
        DbalCompositeTermCompiler $composite_compiler
    ) {
        $this->getCompiler(new AgentTerm())->shouldBe($agent_compiler);
        $this->getCompiler(new DepartmentTerm())->shouldBe($department_compiler);
        $this->getCompiler(new CompositeTerm())->shouldBe($composite_compiler);
    }

    public function it_throws_an_exception_if_no_compiler_found(
        DbalAgentTermCompiler $agent_compiler,
        DbalDepartmentTermCompiler $department_compiler,
        DbalCompositeTermCompiler $composite_compiler
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
