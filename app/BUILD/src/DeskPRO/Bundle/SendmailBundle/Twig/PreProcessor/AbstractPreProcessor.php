<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SendmailBundle\Twig\PreProcessor;

abstract class AbstractPreProcessor
{
    /**
     * @abstract
     *
     * @param string $source
     *
     * @return string
     */
    abstract public function process($source);
}
