<?php

namespace DeskPRO\Bundle\ReportBundle\Reports;

/**
 * Class SplitResult.
 */
class SplitResult
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $results;

    /**
     * Constructor.
     *
     * @param string $title
     * @param array  $results
     */
    public function __construct($title, $results)
    {
        $this->title   = $title;
        $this->results = $results;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }
}
