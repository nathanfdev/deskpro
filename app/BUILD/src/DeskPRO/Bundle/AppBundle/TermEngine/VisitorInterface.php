<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine;

/**
 * A visitor is a service that runs on a Term before compilation.
 */
interface VisitorInterface
{
    /**
     * Visit a term.
     *
     * In the case of a composite term, you may want to be recursive to
     * visit the entire tree of terms.
     *
     * The Terms are mutable.
     *
     * @param TermInterface $term
     */
    public function visit(TermInterface $term);
}
