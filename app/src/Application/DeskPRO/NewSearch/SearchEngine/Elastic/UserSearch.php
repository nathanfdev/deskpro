<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\DeskPRO\NewSearch\SearchEngine\Elastic;

use Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextInterface;
use Application\DeskPRO\NewSearch\SearchEngine\UserSearchInterface;
use Elastica\Filter;
use Elastica\Query;
use Orb\Util\Arrays;
use Elastica\Util as ElasticaUtil;

class UserSearch implements UserSearchInterface
{
    const MAX_LEN = 315;

    /**
     * @var \Elastica\Index
     */
    private $index;

    /**
     * @var ElasticaResultsTransformer
     */
    private $transformer;


    /**
     * @param \Elastica\Index            $index
     * @param ElasticaResultsTransformer $transformer
     */
    public function __construct(\Elastica\Index $index, ElasticaResultsTransformer $transformer)
    {
        $this->index = $index;
        $this->transformer = $transformer;
    }


    /**
     * @param  SearchContextInterface $context
     * @param  string                 $query
     * @param  array                  $options
     * @return ResultSet
     */
    public function search(SearchContextInterface $context, $query, array $options = null)
    {
        $search = $this->index->createSearch();
        $filter = new Filter\BoolOr();

        $limit_types = isset($options['limit_types']) ? $options['limit_types'] : null;
        if ($limit_types && !is_array($limit_types)) {
            $limit_types = explode(',', $limit_types);
            $limit_types = Arrays::func($limit_types, 'trim');
        }
        if ($limit_types) {
            $limit_types = Arrays::removeFalsey($limit_types);
        }

        if ($context->getArticleCategoryIds() && ($limit_types === null || in_array('article', $limit_types))) {
            $search->addType('article');
            $f = new Filter\Bool();
            $f->addMust(new Filter\Term(array('_type' => 'article')));
            $f->addMust(new Filter\Term(array('status' => 'published')));
            $f->addMust(new Filter\Terms('category_ids', $context->getArticleCategoryIds()));
            $f->setBoost('1.5');
            $filter->addFilter($f);
        }
        if ($context->getNewsCategoryIds() && ($limit_types === null || in_array('news', $limit_types))) {
            $search->addType('news');
            $f = new Filter\Bool();
            $f->addMust(new Filter\Term(array('_type' => 'news')));
            $f->addMust(new Filter\Term(array('status' => 'published')));
            $f->addMust(new Filter\Terms('category_id', $context->getNewsCategoryIds()));
            $f->setBoost('1.3');
            $filter->addFilter($f);
        }
        if ($context->getDownloadCategoryIds() && ($limit_types === null || in_array('download', $limit_types))) {
            $search->addType('download');
            $f = new Filter\Bool();
            $f->addMust(new Filter\Term(array('_type' => 'download')));
            $f->addMust(new Filter\Term(array('status' => 'published')));
            $f->addMust(new Filter\Terms('category_id', $context->getDownloadCategoryIds()));
            $f->setBoost('1.5');
            $filter->addFilter($f);
        }
        if ($context->getFeedbackCategoryIds() && ($limit_types === null || in_array('feedback', $limit_types))) {
            $search->addType('feedback');
            $f = new Filter\Bool();
            $f->addMust(new Filter\Term(array('_type' => 'feedback')));
            $f->addMust(new Filter\Term(array('status' => 'published')));
            $f->addMust(new Filter\Terms('category_id', $context->getFeedbackCategoryIds()));
            $filter->addFilter($f);
        }
        if ($context->getPerson() && ($limit_types === null || in_array('ticket', $limit_types))) {
            $search->addType('ticket');
            $f = new Filter\Bool();
            $f->addMust(new Filter\Term(array('_type' => 'ticket')));

            $f2 = new Filter\BoolOr();
            $f2->addFilter(new Filter\Term(array('person_id' => $context->getPerson()->getId())));
            $f2->addFilter(new Filter\Term(array('participants' => $context->getPerson()->getId())));

            if ($context->getPerson()->organization && $context->getPerson()->organization_manager) {
                $f2->addFilter(new Filter\Term(array('organization_id' => $context->getPerson()->organization->getId())));
            }

            $f->addMust($f2);
            $f->setBoost(5);
            $filter->addFilter($f);
        }

        if (!$search->getTypes()) {
            return new ResultSet();
        }

        if (isset($query[self::MAX_LEN])) {
            $query = substr($query, 0, self::MAX_LEN);
        }

        $bool_query = new Query\Bool();
        $qs = $this->getQueryString($query);
        $qs->setDefaultField('_all');
        $qs->setDefaultOperator('AND');
        $bool_query->addMust($qs);

        $sticky_match = new Query\Match();
        $sticky_match->setFieldQuery('sticky_words', $query);
        $sticky_match->setFieldOperator('sticky_words', 'AND');
        $sticky_match->setFieldBoost('sticky_words', 2);
        $bool_query->addShould($sticky_match);

        $filtered_query = new Query\Filtered($qs, $filter);
        $res = $search->search($filtered_query, array('limit' => 500));
        $objects = $this->transformer->transform($res->getResults());

        return new ResultSet($objects);
    }


    /**
     * Makes sure a "query" var is formatted for use with QueryString
     *
     * @param  string $q
     * @return string
     */
    private function escapeQueryStringTerm($q)
    {
        $q = ElasticaUtil::escapeTerm($q);
        $q = str_replace(array('AND', 'OR', 'NOT'), array('and', 'or', 'not'), $q);

        return $q;
    }


    /**
     * Constructs the query string
     *
     * @param $q
     * @return Query\QueryString|Query\MultiMatch
     */
    protected function getQueryString($q)
    {
        $term = $this->escapeQueryStringTerm($q);

        // If we have an equal number of quotes, then
        // they are properly balanced and it's valid so we can
        // accept the "phrase" search
        if (substr_count($term, '\\"') % 2 === 0) {
            $term = str_replace('\\"', '"', $term);
        }

        $queryString = new Query\QueryString($term);

        return $queryString;
    }
}
