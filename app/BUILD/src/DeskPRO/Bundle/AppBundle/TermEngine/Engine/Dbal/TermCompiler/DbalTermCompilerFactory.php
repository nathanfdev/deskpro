<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTermCompilerFactory.
 */
class DbalTermCompilerFactory
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler[]
     */
    private $compilers;

    /**
     * Constructor.
     *
     * @param array $compilers
     */
    public function __construct(array $compilers)
    {
        $this->compilers = $compilers;
    }

    /**
     * @param TermInterface $term
     *
     * @return AbstractDbalTermCompiler
     */
    public function getCompiler(TermInterface $term)
    {
        $class = get_class($term);
        if (array_key_exists($class, $this->compilers)) {
            return $this->compilers[$class];
        }

        throw new \InvalidArgumentException(
            sprintf('DbalTermCompilerFactory: No compiler found for term with class "%s"', $class)
        );
    }
}
