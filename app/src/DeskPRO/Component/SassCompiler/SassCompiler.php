<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
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

namespace DeskPRO\Component\SassCompiler;

use DeskPRO\Component\Filesystem\TmpDir;
use DeskPRO\Component\SassCompiler\CompilerAdapter\CompilerAdapterInterface;
use DeskPRO\Component\SassCompiler\Filter\FilterInterface;
use Symfony\Component\Filesystem\Filesystem;

class SassCompiler
{
    /**
     * @var CompilerAdapterInterface
     */
    private $compiler;

    /**
     * @var FilterInterface[] array
     */
    private $filters = array();

    /**
     * @var string|null
     */
    private $tmp_dir = null;

    /**
     * @param CompilerAdapterInterface $compiler
     * @param string|null $tmp_dir
     */
    function __construct(CompilerAdapterInterface $compiler, $tmp_dir = null)
    {
        $this->compiler = $compiler;
        $this->tmp_dir = $tmp_dir;
    }


    /**
     * @param FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Creates a temporary directory where files in memory
     * can be written to.
     *
     * @return string
     */
    private function writeTmpProjectDir(SassProject $project)
    {
        $tmp_dir = TmpDir::create($this->tmp_dir);
        $fs = new Filesystem();

        foreach ($project->getFileSources() as $name => $source) {
            $fs->dumpFile($tmp_dir . DIRECTORY_SEPARATOR . $name, $source, null);
        }

        return $tmp_dir;
    }

    /**
     * @param SassProject $project
     */
    public function compileProject(SassProject $project)
    {
        $p = new SassProject();

        foreach ($project->getFileSources() as $file => $source) {
            foreach ($this->filters as $f) {
                $source = $f->preProcessSource($file, $source);
            }
            $p->addFileSource($file, $source);
        }

        $tmp_dir = $this->writeTmpProjectDir($p);
        $p->addIncludePath($tmp_dir);

        foreach ($project->getIncludePaths() as $inc) {
            $p->addIncludePath($inc);
        }

        $result = $this->compiler->compile($tmp_dir . DIRECTORY_SEPARATOR . '__main__.scss', $p->getIncludePaths());

        foreach ($this->filters as $f) {
            $result = $f->postProcessResult($result);
        }

        return $result;
    }
}