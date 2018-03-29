<?php

/**
 * DeskPRO.
 *
 * @category ContentSearch
 */

namespace Application\DeskPRO\ContentSearch;

use Application\DeskPRO\App;

/**
 * When an entity is updated, this should be called from a post* method to update its index.
 */
class IndexUpdater
{
    /**
     * @var \Application\DeskPRO\ContentSearch\ContentSearchable
     */
    protected $entity;

    public function __construct(\Application\DeskPRO\ContentSearch\ContentSearchable $entity)
    {
        $this->entity = $entity;
    }

    public function updateIndex()
    {
        App::getDb()->beginTransaction();

        App::getDb()->delete('content_search', ['id' => $this->entity->getSearchId()]);
        App::getDb()->delete('content_search_attributes', ['id' => $this->entity->getSearchId()]);

        App::getDb()->insert('content_search', [
            'id'      => $this->entity->getSearchId(),
            'content' => $this->entity->getSearchContent(),
        ]);

        $attr = $this->entity->getSearchAttributes();
        if ($attr) {
            foreach ($attr as $k => $v) {
                App::getDb()->insert('content_search', [
                    'search_id'    => $this->entity->getSearchId(),
                    'attribute_id' => $k,
                    'content'      => $v,
                ]);
            }
        }

        App::getDb()->commit();
    }
}
