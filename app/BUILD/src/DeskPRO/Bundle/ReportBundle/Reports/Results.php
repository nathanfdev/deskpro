<?php

namespace DeskPRO\Bundle\ReportBundle\Reports;

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
    protected $results = [];

    /**
     * @var ResultMetadata
     */
    protected $metadata;

    /**
     * @return bool
     */
    public function isEmpty()
    {
        if (empty($this->results)) {
            return true;
        }

        if ($this->hasSplitResults()) {
            foreach ($this->results as $r) {
                if (!empty($r[0])) {
                    return false;
                }
            }
        } else {
            if (!empty($this->results[0][0])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets the results to a single result set.
     *
     * @param array $results
     */
    public function setResults(array $results)
    {
        $this->results = [0 => [$results, null]];
    }

    /**
     * Adds a split result set.
     *
     * @param array $results
     * @param array $split   Row of data for the split header
     */
    public function addSplitResults(array $results, array $split)
    {
        $this->results[] = [$results, $split];
    }

    /**
     * @return bool
     */
    public function hasSplitResults()
    {
        $total = count($this->results);

        if ($total > 1) {
            return true;
        }
        if ($total < 1) {
            return false;
        }

        return $this->results[0][1] !== null;
    }

    /**
     * Gets all split result sets.
     *
     * @return array
     */
    public function getSplitResults()
    {
        return $this->results;
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
        if (!$this->results) {
            return [];
        }

        if ($this->hasSplitResults()) {
            throw new \Exception('Has split results but trying to get base results');
        }

        return $this->results[0][0];
    }

    /**
     * @return ResultMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param ResultMetadata $metadata
     */
    public function setMetadata(ResultMetadata $metadata)
    {
        $this->metadata = $metadata;
    }
}
