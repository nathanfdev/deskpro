<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\SassCompiler\Compiler;

use DeskPRO\Component\SassCompiler\SassProject;

interface CompilerInterface
{
    /**
     * @param SassProject $project
     *
     * @return string
     */
    public function compile(SassProject $project);
}
