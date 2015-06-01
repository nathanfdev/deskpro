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
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Component\Util\ListUtils;

use DeskPRO\Component\SassCompiler\CompilerAdapter\NodeSassAdapter;
use DeskPRO\Component\SassCompiler\CompilerAdapter\SimpleImportAdapter;
use DeskPRO\Component\SassCompiler\SassCompiler;
use DeskPRO\Component\SassCompiler\SassProject;
use DpTest\DeskProTestCase;

class SassCompilerTest extends DeskProTestCase
{
    public function testCompiler()
    {
        $adapter = new SimpleImportAdapter();
        $compiler = new SassCompiler($adapter, dp_get_tmp_dir());

        $proj = new SassProject();
        $proj->setSource("SOURCE\n@import 'file_a.css';\n@import 'file_b.css';");
        $proj->addFileSource('file_a.css', "A");
        $proj->addFileSource('file_b.css', "B");

        $this->assertEquals("SOURCE\nA\nB", trim($compiler->compileProject($proj)));
    }

    public function testNodeSassCompiler()
    {
        $bin_path = 'node-sass';

        $out = $ret = null;
        exec($bin_path . ' --version', $out, $ret);
        if ($ret != 0) {
            // not installed on this server
            return;
        }

        $adapter = new NodeSassAdapter($bin_path);
        $compiler = new SassCompiler($adapter, dp_get_tmp_dir());

        $source = <<<SRC
@import "a";
body { background: \$bg-color; }
SRC;

        $source_a = <<<SRC
\$bg-color: #000;
SRC;

        $proj = new SassProject();
        $proj->setSource($source);
        $proj->addFileSource('a.scss', $source_a);

        $result_expect = <<<SRC
body { background: #000; }
SRC;

        $this->assertEquals($this->normalizeString($result_expect), $this->normalizeString($compiler->compileProject($proj)));
    }

    private function normalizeString($s)
    {
        return strtolower(preg_replace('#\s#', '', $s));
    }
}