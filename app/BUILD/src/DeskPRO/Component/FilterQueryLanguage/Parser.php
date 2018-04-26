<?php

namespace DeskPRO\Component\FilterQueryLanguage;

class Parser
{
    /**
     * @param string $fqlQuery
     *
     * @return Query\Query
     */
    public function parseQuery($fqlQuery)
    {
        $qp = new QueryParser($fqlQuery);

        return $qp->parse();
    }
}
