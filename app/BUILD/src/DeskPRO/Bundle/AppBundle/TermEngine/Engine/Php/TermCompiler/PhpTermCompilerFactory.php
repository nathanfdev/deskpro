<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class PhpTermCompilerFactory
{
    /**
     * @var AbstractPhpTermCompiler[]
     */
    private $compilers;

    public function __construct(array $compilers)
    {
        $this->compilers = $compilers;
    }

    public function getCompiler(TermInterface $term)
    {
        $class = get_class($term);
        if (array_key_exists($class, $this->compilers)) {
            return $this->compilers[$class];
        }

        throw new \InvalidArgumentException(
            sprintf('PhpTermCompilerFactory: No compiler found for term with class "%s"', $class)
        );
    }
}
