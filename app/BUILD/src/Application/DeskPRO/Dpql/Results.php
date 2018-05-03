<?php

namespace Application\DeskPRO\Dpql;

/**
 * Represents the results from a DPQL query (which may span multiple MySQL queries).
 */
class Results
{
    /**
     * List of result sets. Each element is another array with 2 elements:
     *  - 0: results set (multiple rows, with each row 0-base keyed)
     *  - 1: split results row (0-based keyed array) or null for non-split results.
     *
     * @var array
     */
    protected $_results = [];

    /**
     * Sets the results to a single result set.
     *
     * @param array $results
     */
    public function setResults(array $results)
    {
        $this->_results = [0 => [$results, null]];
    }

    /**
     * Adds a split result set.
     *
     * @param array $results
     * @param array $split   Row of data for the split header
     */
    public function addSplitResults(array $results, array $split)
    {
        $this->_results[] = [$results, $split];
    }

    /**
     * @return bool
     */
    public function hasSplitResults()
    {
        $total = count($this->_results);

        if ($total > 1) {
            return true;
        }
        if ($total < 1) {
            return false;
        }

        return $this->_results[0][1] !== null;
    }

    /**
     * Gets all split result sets.
     *
     * @return array
     */
    public function getSplitResults()
    {
        return $this->_results;
    }

    /**
     * Gets the single result set (errors if multiple result sets).
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getResults()
    {
        if (!$this->_results) {
            return [];
        }

        if ($this->hasSplitResults()) {
            throw new \Exception('Has split results but trying to get base results');
        }

        return $this->_results[0][0];
    }
}
