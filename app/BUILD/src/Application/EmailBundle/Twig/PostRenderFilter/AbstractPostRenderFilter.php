<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Twig\PostRenderFilter;

abstract class AbstractPostRenderFilter
{
    /**
     * @abstract
     *
     * @param string $name
     * @param string $code
     *
     * @return string
     */
    abstract public function process($name, $code);
}
