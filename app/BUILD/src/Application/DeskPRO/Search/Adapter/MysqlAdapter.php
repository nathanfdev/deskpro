<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\Adapter;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Search\Searcher\Mysql\AgentCombinedSearcher;
use Application\DeskPRO\Search\Searcher\Mysql\ContentSearcher;
use Orb\Util\Strings;

/**
 * Search adapter.
 */
class MysqlAdapter extends AbstractAdapter
{
    /** @var array */
    public static $capabilities = [
        'searcher_content', 'searcher_content_labels',
    ];

    public function __construct()
    {
        $this->addContentTypeMap(Article::class, 'article');
        $this->addContentTypeMap(Download::class, 'download');
        $this->addContentTypeMap(Feedback::class, 'feedback');
        $this->addContentTypeMap(News::class, 'news');
        $this->addContentTypeMap(Topic::class, 'topic');
    }

    /**
     * Delete the specified docs from the index.
     *
     * @param  $documents
     */
    public function deleteDocumentsFromIndex(array $documents)
    {
        foreach ($documents as $doc) {
            App::getDb()->delete('content_search', [
                'object_type' => $doc->getContentTypeName(),
                'object_id'   => $doc->getId(),
            ]);
            App::getDb()->delete('content_search_attribute', [
                'object_type' => $doc->getContentTypeName(),
                'object_id'   => $doc->getId(),
            ]);
        }
    }

    /**
     * Update the search index with the specified docs.
     *
     * @param  $documents
     */
    public function updateDocumentsInIndex(array $documents)
    {
        foreach ($documents as $doc) {
            if ($doc->isMarkedRemove()) {
                $this->deleteDocumentsFromIndex([$doc]);
                continue;
            }

            $data = $doc->getData();

            App::getDb()->executeUpdate('
                REPLACE INTO content_search
                SET object_type = ?, object_id = ?, content = ?
            ', [$doc->getContentTypeName(), $doc->getId(), $data['content']]);

            unset($data['content']);

            foreach ($data as $k => $v) {
                App::getDb()->executeUpdate('
                    DELETE FROM content_search_attribute WHERE object_type = ? AND object_id = ? AND content = ?
                ', [$doc->getContentTypeName(), $doc->getId(), $v]);
                App::getDb()->executeUpdate('
                    REPLACE INTO content_search_attribute
                    SET object_type = ?, object_id = ?, attribute_id = ?, content = ?
                ', [$doc->getContentTypeName(), $doc->getId(), $k, $v]);
            }
        }
    }

    /**
     * Create a new instance of a contenttype object.
     *
     * Factory method.
     *
     * @param string $type_name
     *
     * @return \Application\DeskPRO\Search\ContentType\ContentTypeInterface
     */
    protected function createContentType($type_name)
    {
        // All of our contenttype classes are dumb, so they dont need
        // any special initialization. We can simply initialize them with the classname.

        $type_name = str_replace('_', '-', $type_name);
        $type_name = ucfirst(Strings::dashToCamelCase($type_name));

        $classname = 'Application\\DeskPRO\\Search\\ContentType\\Mysql\\'.$type_name;
        $obj       = new $classname();

        return $obj;
    }

    /**
     * Get a content searcher.
     *
     * Factory method.
     *
     * @return \Application\DeskPRO\Search\Searcher\ContentSearcherInterface
     */
    public function getContentSearcher()
    {
        $searcher = new ContentSearcher();
        $searcher->setPersonContext($this->getPersonContext());

        return $searcher;
    }

    /**
     * Get the combined agent searcher.
     *
     * Factory method.
     *
     * @return \Application\DeskPRO\Search\Searcher\Mysql\AgentCombinedSearcher
     */
    public function getAgentCombinedSearcher()
    {
        $searcher = new AgentCombinedSearcher();
        $searcher->setPersonContext($this->getPersonContext());

        return $searcher;
    }

    /**
     * Delete all objects from the index of a particular content type.
     *
     * @param string $type_name
     */
    public function deleteContentTypeFromIndex($type_name)
    {
        App::getDb()->executeUpdate('
            DELETE FROM content_search WHERE object_type = ?
        ', [$type_name]);
    }

    /**
     * Labels are added to the fulltext index and then fetched with a fulltext match
     * in "boolean" mode, which is one of the only ways to efficiently fetch labels
     * with intersections or unions (ie. content with two labels, or with one label but without another).
     *
     * But certain words are stripped for stop words, and mysql doesn't handle dashes very well,
     * and when fetching labels we don't want to confuse them with other words. So
     * we "encode" them as these hashes, so we can search for "+lbl1232984rf" specifically.
     *
     * @param  $label
     *
     * @return string
     */
    public static function encodeLabel($label)
    {
        $label = 'lbl'.md5(strtolower(trim($label)));

        return $label;
    }

    /**
     * @param  $label
     *
     * @return string
     */
    public static function encodeProperty($k, $v)
    {
        $label = md5(strtolower($k.$v));

        return $label;
    }
}
