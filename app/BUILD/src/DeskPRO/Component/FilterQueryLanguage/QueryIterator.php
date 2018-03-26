<?php

namespace DeskPRO\Component\FilterQueryLanguage;

use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\TermGroup;

/**
 * Use this iterator to iterate over every node of a query.
 */
class QueryIterator extends \ArrayIterator implements \RecursiveIterator
{
    public function __construct(Query\Query $query)
    {
        if ($query->root instanceof TermGroup) {
            parent::__construct($query->root->terms);
        } else {
            parent::__construct([$query->root]);
        }
    }

    public function hasChildren()
    {
        return $this->current() instanceof TermGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->current()->terms;
    }

    /**
     * Iterate through a query and collect the identities of all
     * referenced fields.
     *
     * @param Query\Query $query
     *
     * @return string[]
     */
    public static function collectFieldIds(Query\Query $query)
    {
        $fields = [];

        foreach (new self($query) as $n) {
            if ($n instanceof Term) {
                $fields[] = $n->field->identity;
            }
        }

        return $fields;
    }
}
