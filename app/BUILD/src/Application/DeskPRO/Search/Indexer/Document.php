<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\Indexer;

use Orb\Util\Strings;

/**
 * A document represents something that we'll insert into the index.
 */
class Document implements DocumentInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $content_type;

    /**
     * Data array of properties.
     *
     * @var array
     */
    protected $data;

    /**
     * @var bool
     */
    protected $mark_removed = false;

    /**
     * @param array $info
     *
     * @return \Application\DeskPRO\Search\Indexer\Document
     */
    public static function newFromArray(array $info)
    {
        $id           = $info['id'];
        $content_type = $info['content_type'];

        unset($info['id'], $info['content_type']);

        $do_remove = false;
        if (isset($info['remove'])) {
            $do_remove = true;
            unset($info['remove']);
        }

        if (Strings::hasPhpUtf8()) {
            foreach ($info as &$v) {
                if (is_string($v)) {
                    $v = Strings::decodeHtmlEntities($v);
                    $v = Strings::decodeUnicodeEntities($v);
                    $v = Strings::utf8_accents_to_ascii($v);
                }
            }
            unset($v);
        }

        $obj = new self($id, $content_type, $info);

        if ($do_remove) {
            $obj->markRemove();
        }

        return $obj;
    }

    public function __construct($id, $content_type, array $data = [])
    {
        $this->id           = $id;
        $this->content_type = $content_type;
        $this->data         = $data;
    }

    /**
     * Get the unique ID for this document in the index.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the type of document.
     *
     * @return string
     */
    public function getContentTypeName()
    {
        return $this->content_type;
    }

    /**
     * Get the data to index.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Mark document for removal.
     */
    public function markRemove()
    {
        $this->mark_removed = true;
    }

    /**
     * @return bool
     */
    public function isMarkedRemove()
    {
        return $this->mark_removed;
    }
}
