<?php

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
