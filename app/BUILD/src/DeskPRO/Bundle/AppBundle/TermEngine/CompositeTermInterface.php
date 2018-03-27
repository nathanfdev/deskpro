<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine;

interface CompositeTermInterface extends TermInterface
{
    public function getTerms();

    public function addTerm(TermInterface $term);
}
