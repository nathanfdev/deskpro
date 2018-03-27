<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

/**
 * Abstract base for parts of a DPQL statement.
 */
abstract class AbstractPart implements DpqlStatementPartInterface
{
    /**
     * Gets the part stack to pass to a child part (includes this object).
     *
     * @param AbstractPart[] $stack
     *
     * @return AbstractPart[]
     */
    public function getChildStack(array $stack)
    {
        $childStack = $stack;
        array_unshift($childStack, $this);

        return $childStack;
    }
}
