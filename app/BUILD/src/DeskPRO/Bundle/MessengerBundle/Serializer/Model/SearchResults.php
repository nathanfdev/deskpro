<?php

namespace DeskPRO\Bundle\MessengerBundle\Serializer\Model;

/**
 * Class SearchResults
 */
class SearchResults implements MessengerModelInterface
{
    /**
     * @var array
     */
    private $results;

    /**
     * SearchResults constructor.
     *
     * @param array $results
     */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($r) {
            return $r->toArray();
        }, $this->results);
    }
}
