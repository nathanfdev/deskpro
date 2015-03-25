<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalAgentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalCompositeTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalDepartmentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\DbalTermCompilerFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\DbalTermCompilerFactory
 */
class DbalTermCompilerFactorySpec extends ObjectBehavior
{
    function let(
        DbalAgentTermCompiler $agent_compiler,
        DbalDepartmentTermCompiler $department_compiler,
        DbalCompositeTermCompiler $composite_compiler
    )
    {
        $this->beConstructedWith(
            array(
                'DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm' => $agent_compiler,
                'DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm' => $department_compiler,
                'DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm' => $composite_compiler
            )
        );
    }

    function it_finds_the_right_compiler(
        DbalAgentTermCompiler $agent_compiler,
        DbalDepartmentTermCompiler $department_compiler,
        DbalCompositeTermCompiler $composite_compiler
    )
    {
        $this->getCompiler(New AgentTerm())->shouldBe($agent_compiler);
        $this->getCompiler(new DepartmentTerm())->shouldBe($department_compiler);
        $this->getCompiler(new CompositeTerm())->shouldBe($composite_compiler);
    }

    function it_throws_an_exception_if_no_compiler_found(
        DbalAgentTermCompiler $agent_compiler,
        DbalDepartmentTermCompiler $department_compiler,
        DbalCompositeTermCompiler $composite_compiler
    )
    {
        $this->shouldThrow('\InvalidArgumentException')->during('getCompiler', array(new FakeTerm()));
    }
}

class FakeTerm extends AbstractTerm
{
    public function setDefaultOptions(OptionsResolver $resolver)
    {
    }
}