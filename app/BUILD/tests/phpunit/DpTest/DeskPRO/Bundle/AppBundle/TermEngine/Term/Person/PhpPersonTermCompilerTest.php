<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Person\PersonTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Person\PhpPersonTermCompiler;
use DpTest\DeskProTestCase;

class PhpPersonTermCompilerTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $this->assertInstanceOf(PhpPersonTermCompiler::class, new PhpPersonTermCompiler());
    }

    /**
     * @test
     */
    public function it_should_extend_AbstractPhpTermCompiler()
    {
        $this->assertContains(AbstractPhpTermCompiler::class, class_parents(PhpPersonTermCompiler::class));
    }

    /**
     * @test
     */
    public function it_should_compile_a_PersonTerm_into_a_PhpCheck()
    {
        $term     = new PersonTerm(['person_ids' => [1]]);
        $compiler = new PhpPersonTermCompiler();
        $this->assertInstanceOf(PhpCheck::class, $compiler->compile($term));
    }
}
