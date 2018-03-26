<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Search\Indexer\Initializer\Mysql;

use Application\DeskPRO\App;
use Application\DeskPRO\Search\Indexer\Initializer\ContentInitializer as BaseContentInitializer;

class ContentInitializer extends BaseContentInitializer
{
    public function preRun()
    {
        App::getDb()->exec("DELETE FROM content_search WHERE object_type IN ('article','download','feedback','news','topic')");
        App::getDb()->exec("DELETE FROM content_search_attribute WHERE object_type IN ('article','download','feedback','news','topic')");
    }
}
