<?php

namespace Application\DeskPRO\NewSearch\SearchEngine;

class SearchEngine implements SearchEngineInterface
{
    /**
     * @var UserSearchInterface
     */
    private $user_search;

    /**
     * @param UserSearchInterface $user_search
     */
    public function __construct(UserSearchInterface $user_search)
    {
        $this->user_search = $user_search;
    }

    /**
     * @return UserSearchInterface
     */
    public function getUserSearch()
    {
        return $this->user_search;
    }
}
