<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php;

class PhpEngineEvents
{
    /**
     * This is thrown by the engine during evaluate() before the compiler compiles the terms of the filter.
     *
     * It fires a PhpEnginePreCompileEvent
     */
    const PRE_COMPILE = 'php_engine.pre_compile';

    /**
     * This is thrown by the engine during evaluate() after the compiler compiles the terms of the filter
     * into a PhpClass, but before the PhpClass is saved to disk.
     *
     * It fires a PhpEnginePostCompileEvent
     */
    const POST_COMPILE = 'php_engine.post_compile';
}
