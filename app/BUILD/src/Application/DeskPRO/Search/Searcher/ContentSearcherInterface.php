<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\Searcher;

/**
 * Interface for 'ContentSearcher'.
 *
 * The content searcher searches: articles, downloads, feedback, news
 */
interface ContentSearcherInterface
{
    /**
     * A natural text query.
     *
     * @param  $query
     *
     * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
     */
    public function query($query_text, $per_page = 25, $page = 1, array $limit_types = null, $top = false);

    /**
     * Fetch lablled content.
     *
     * @param  $labels
     *
     * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
     */
    public function labelled(array $labels, $per_page = 25, $page = 1, array $limit_types = null);

    /**
     * Find content similar to $content.
     *
     * @param string $content
     * @param array  $in_types Types you want to search in, or null for all
     *
     * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
     */
    public function similarContent($content, array $in_types = []);

    /**
     * Results for the "omnisearch" search box.
     *
     * @param string $content
     *
     * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
     */
    public function omnisearch($query_text);
}
