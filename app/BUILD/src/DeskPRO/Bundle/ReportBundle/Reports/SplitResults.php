<?php

namespace DeskPRO\Bundle\ReportBundle\Reports;

/**
 * Class SplitResults.
 */
class SplitResults
{
    /**
     * @var array
     */
    private $results;

    /**
     * Constructor.
     *
     * @param array $results
     */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /**
     * @return SplitResult[]
     */
    public function getResults()
    {
        return $this->results;
    }
}
