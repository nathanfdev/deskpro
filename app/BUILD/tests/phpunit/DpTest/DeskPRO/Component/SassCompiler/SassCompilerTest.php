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

namespace DpTest\DeskPRO\Component\SassCompiler;

use DeskPRO\Component\SassCompiler\Compiler\ScssPhpCompiler;
use DeskPRO\Component\SassCompiler\SassProject;
use DpTest\DeskProTestCase;

class SassCompilerTest extends DeskProTestCase
{
    public function testCompiler()
    {
        $compiler = new ScssPhpCompiler();
        $project  = new SassProject();
        $project->setSourceFile(__DIR__.'/data/sample.scss');

        $res = $compiler->compile($project);
        $this->assertStringMatchesFormatFile(__DIR__.'/data/sample.css', $res);
    }

    public function testImportCompiler()
    {
        $compiler = new ScssPhpCompiler();
        $project  = new SassProject();
        $project->setSourceFile(__DIR__.'/data/importtest/main.scss');

        $res = $compiler->compile($project);
        $this->assertStringMatchesFormatFile(__DIR__.'/data/importtest/main.css', $res);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnsafeImportCompiler()
    {
        $compiler = new ScssPhpCompiler([
            'compiler_options' => [
                'error_load_file' => 'throw',
            ],
        ]);
        $project = new SassProject();
        $project->setSource('@import "/etc/passwd"');
        $project->addIncludePath(__DIR__);

        $compiler->compile($project);
    }

    public function testImportBadCompiler()
    {
        $compiler = new ScssPhpCompiler();
        $project  = new SassProject();
        $project->setSourceFile(__DIR__.'/data/importtest_bad/main.scss');

        $res = $compiler->compile($project);
        $this->assertStringMatchesFormatFile(__DIR__.'/data/importtest_bad/main.css', $res);
    }

    public function testImportOverrideCompiler()
    {
        $compiler = new ScssPhpCompiler();
        $project  = new SassProject();
        $project->setSourceFile(__DIR__.'/data/importtest/main.scss');
        $project->addFileSource(__DIR__.'/data/importtest/vars.scss', '$color: green;');

        $res = $compiler->compile($project);
        $this->assertStringMatchesFormatFile(__DIR__.'/data/importtest/main_override.css', $res);
    }

    public function testImportVirtualCompiler()
    {
        $compiler = new ScssPhpCompiler();
        $project  = new SassProject();
        $project->setSourceFile(__DIR__.'/data/importtest_virtual/main.scss');
        $project->addFileSource('vars.scss', '$color: red;');

        $res = $compiler->compile($project);
        $this->assertStringMatchesFormatFile(__DIR__.'/data/importtest_virtual/main.css', $res);
    }

    public function testImportVirtualAliasCompiler()
    {
        $compiler = new ScssPhpCompiler();
        $project  = new SassProject();
        $project->setSourceFile(__DIR__.'/data/importtest_virtual_alias/main.scss');
        $project->addFileSource('vars.scss', '$color: red;');
        $project->addFileSource('foobar.scss', '@alias:vars.scss');

        $res = $compiler->compile($project);
        $this->assertStringMatchesFormatFile(__DIR__.'/data/importtest_virtual_alias/main.css', $res);
    }
}
