<?php

namespace Application\DeskPRO\NewSearch\SearchEngine\Elastic;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextInterface;
use Application\DeskPRO\NewSearch\SearchEngine\UserSearchInterface;
use Elastica\Index;
use Elastica\Query;
use Elastica\Util as ElasticaUtil;
use Orb\Util\Arrays;

class UserSearch implements UserSearchInterface
{
    const MAX_LEN         = 315;
    const MAX_LEN_CONTENT = 2000;
    const LIMIT           = 20;

    /**
     * @var Index
     */
    private $index;

    /**
     * @var ElasticaResultsTransformer
     */
    private $transformer;

    /**
     * @param Index                      $index
     * @param ElasticaResultsTransformer $transformer
     */
    public function __construct(Index $index, ElasticaResultsTransformer $transformer)
    {
        $this->index       = $index;
        $this->transformer = $transformer;
    }

    /**
     * @param SearchContextInterface $context
     * @param string                 $query
     * @param array                  $options
     *
     * @return ResultSet
     */
    public function search(SearchContextInterface $context, $query, array $options = null)
    {
        $search = $this->index->createSearch();
        $filter = new Query\BoolQuery();

        $limitTypes = isset($options['limit_types']) ? $options['limit_types'] : null;
        if ($limitTypes && !is_array($limitTypes)) {
            $limitTypes = explode(',', $limitTypes);
            $limitTypes = Arrays::func($limitTypes, 'trim');
        }
        if ($limitTypes) {
            $limitTypes = Arrays::removeFalsey($limitTypes);
        }

        if ($context->getArticleCategoryIds() && ($limitTypes === null || in_array('article', $limitTypes))) {
            $search->addType('article');
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'article']));
            $f->addMust(new Query\Term(['status' => 'published']));
            $f->addMust(new Query\Terms('category_ids', $context->getArticleCategoryIds()));
            $filter->addShould($f);
        }
        if ($context->getNewsCategoryIds() && ($limitTypes === null || in_array('news', $limitTypes))) {
            $search->addType('news');
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'news']));
            $f->addMust(new Query\Term(['status' => 'published']));
            $f->addMust(new Query\Terms('category_id', $context->getNewsCategoryIds()));
            $filter->addShould($f);
        }
        if ($context->getDownloadCategoryIds() && ($limitTypes === null || in_array('download', $limitTypes))) {
            $search->addType('download');
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'download']));
            $f->addMust(new Query\Term(['status' => 'published']));
            $f->addMust(new Query\Terms('category_id', $context->getDownloadCategoryIds()));
            $filter->addShould($f);
        }
        if ($context->getFeedbackCategoryIds() && ($limitTypes === null || in_array('feedback', $limitTypes))) {
            $search->addType('feedback');
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'feedback']));
            $f->addMustNot(new Query\Term(['status' => 'hidden']));
            $f->addMust(new Query\Terms('category_id', $context->getFeedbackCategoryIds()));
            $filter->addShould($f);
        }
        if ($context->getGuideIds() && ($limitTypes === null || in_array('topic', $limitTypes))) {
            $search->addType('topic');
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'topic']));
            $f->addMustNot(new Query\Term(['status' => 'hidden']));
            $f->addMust(new Query\Terms('guide_id', $context->getGuideIds()));
            $filter->addShould($f);
        }
        if ($context->getPerson() && ($limitTypes === null || in_array('ticket', $limitTypes))) {
            $search->addType('ticket');
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'ticket']));
            $f->addMust(new Query\Term(['brand' => $context->getBrand()->getId()]));

            $f2 = new Query\BoolQuery();
            $f2->addShould(new Query\Term(['person_id' => $context->getPerson()->getId()]));
            if (!$context->getPerson()->isAgent()) {
                $f2->addShould(new Query\Term(['participants' => $context->getPerson()->getId()]));
            }

            if ($context->getPerson()->organization && $context->getPerson()->organization_manager) {
                $f2->addShould(new Query\Term(['organization_id' => $context->getPerson()->organization->getId()]));
            }

            $f->addMust($f2);
            $filter->addShould($f);
        }
        if ($context->getPerson() && ($limitTypes === null || in_array('chat_conversation', $limitTypes))) {
            $search->addType('chat_conversation');

            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'chat_conversation']));
            $f->addMust(new Query\Term(['person' => $context->getPerson()->getId()]));

            $filter->addShould($f);
        }

        if (!$search->getTypes()) {
            return new ResultSet();
        }

        if (isset($query[self::MAX_LEN])) {
            $query = substr($query, 0, self::MAX_LEN);
        }

        $boolQuery = new Query\BoolQuery();
        $qs        = $this->getQueryString($query);
        $qs->setDefaultField('_all');
        $qs->setFields(['_id', 'ref', 'title', 'labels', 'content', 'messages']);
        $qs->setDefaultOperator('AND');
        $boolQuery->addMust($qs);

        $match = new Query\Match();
        $match->setFieldQuery('_type', 'ticket');
        $match->setFieldBoost('_type', 1000);
        $boolQuery->addShould($match);

        $match = new Query\Match();
        $match->setFieldQuery('_id', $query);
        $match->setFieldBoost('_id', 1000);
        $boolQuery->addShould($match);

        $match = new Query\Match();
        $match->setFieldQuery('ref', $query);
        $match->setFieldBoost('ref', 3);
        $boolQuery->addShould($match);

        $stickyMatch = new Query\Match();
        $stickyMatch->setFieldQuery('sticky_words', $query);
        $stickyMatch->setFieldOperator('sticky_words', 'AND');
        $stickyMatch->setFieldBoost('sticky_words', 2);
        $boolQuery->addShould($stickyMatch);

        $filteredQuery = new Query\BoolQuery();
        $filteredQuery->addMust($boolQuery);
        $filteredQuery->addFilter($filter);

        $res     = $search->search($filteredQuery, ['limit' => self::LIMIT]);
        $objects = $this->transformer->transform($res->getResults());

        if ($context->getPerson() && !$context->getPerson()->is_agent) {
            $objects = array_filter($objects, function ($ticket) {
                if ($ticket instanceof Ticket && !($ticket->date_last_agent_reply || $ticket->date_last_user_reply)) {
                    return false;
                }

                return true;
            });
        }

        return new ResultSet($objects);
    }

    /**
     * @param SearchContextInterface $context
     * @param string                 $content
     * @param array                  $options
     *
     * @return ResultSet
     */
    public function similarTo(SearchContextInterface $context, $content, array $options = null)
    {
        $search    = $this->index->createSearch();
        $boolQuery = new Query\BoolQuery();

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
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'article']));
            $f->addMust(new Query\Term(['_type' => 'article']));
            $f->addMust(new Query\Term(['status' => 'published']));
            $f->addMust(new Query\Terms('category_ids', $context->getArticleCategoryIds()));
            $boolQuery->addShould($f);
        }
        if ($context->getNewsCategoryIds() && ($limit_types === null || in_array('news', $limit_types))) {
            $search->addType('news');
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'news']));
            $f->addMust(new Query\Term(['status' => 'published']));
            $f->addMust(new Query\Terms('category_id', $context->getNewsCategoryIds()));
            $boolQuery->addShould($f);
        }
        if ($context->getDownloadCategoryIds() && ($limit_types === null || in_array('download', $limit_types))) {
            $search->addType('download');
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'download']));
            $f->addMust(new Query\Term(['status' => 'published']));
            $f->addMust(new Query\Terms('category_id', $context->getDownloadCategoryIds()));
            $boolQuery->addShould($f);
        }
        if ($context->getFeedbackCategoryIds() && ($limit_types === null || in_array('feedback', $limit_types))) {
            $search->addType('feedback');
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'feedback']));
            $f->addMustNot(new Query\Term(['status' => 'hidden']));
            $f->addMust(new Query\Terms('category_id', $context->getFeedbackCategoryIds()));
            $boolQuery->addShould($f);
        }
        if ($context->getGuideIds() && ($limit_types === null || in_array('topic', $limit_types))) {
            $search->addType('topic');
            $f = new Query\BoolQuery();
            $f->addMust(new Query\Term(['_type' => 'topic']));
            $f->addMustNot(new Query\Term(['status' => 'hidden']));
            $f->addMust(new Query\Terms('guide_id', $context->getGuideIds()));
            $boolQuery->addShould($f);
        }

        if (!$search->getTypes()) {
            return new ResultSet();
        }

        if (isset($content[self::MAX_LEN_CONTENT])) {
            $content = substr($content, 0, self::MAX_LEN_CONTENT);
        }

        $likeQuery = new Query\MoreLikeThis();
        $likeQuery->setFields(['title', 'labels', 'content']);
        $likeQuery->setLike($this->escapeQueryStringTerm($content));
        $likeQuery->setMinTermFrequency(1);
        $likeQuery->setMinDocFrequency(1);

        $filteredQuery = new Query\BoolQuery();
        $filteredQuery->addMust($likeQuery);
        $filteredQuery->addFilter($boolQuery);

        $res     = $search->search($filteredQuery, ['limit' => self::LIMIT]);
        $objects = $this->transformer->transform($res->getResults());

        return new ResultSet($objects);
    }

    /**
     * Makes sure a "query" var is formatted for use with QueryString.
     *
     * @param string $q
     *
     * @return string
     */
    private function escapeQueryStringTerm($q)
    {
        $q = ElasticaUtil::escapeTerm($q);
        $q = str_replace(['AND', 'OR', 'NOT'], ['and', 'or', 'not'], $q);

        return $q;
    }

    /**
     * Constructs the query string.
     *
     * @param $q
     *
     * @return Query\QueryString|Query\QueryString
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

        // ES does not skip short words and returns empty results if they are in the query string
        // just remove them
        $term = preg_replace('/\b.{1,2}\b/', ' ', $term);
        if (!is_string($term)) {
            $term = '';
        }

        $queryString = new Query\QueryString();
        $queryString->setQuery($term);

        return $queryString;
    }
}
