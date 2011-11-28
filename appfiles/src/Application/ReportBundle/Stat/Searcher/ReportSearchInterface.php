<?php

namespace Application\ReportBundle\Stat\Searcher;

use Application\ReportBundle\Stat\Base\QueryBuilder;

interface ReportSearchInterface
{
        public function buildQuery(QueryBuilder $query);
}