<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\SearcherResult;

/**
 * Search adapter.
 */
class Result implements ResultInterface
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
     * @var string
     */
    protected $highlighed;

    /**
     * @var array
     */
    protected $data = [];

    public static function newFromArray(array $info)
    {
        $id           = $info['id'];
        $content_type = $info['content_type'];
        $highlighted  = !empty($info['highlighted']) ? $info['highlighted'] : null;

        unset($info['id'], $info['content_type'], $info['highlighted']);

        return new self($id, $content_type, $info, $highlighted);
    }

    public function __construct($id, $content_type, array $data = [], $highlighted = null)
    {
        $this->id           = $id;
        $this->content_type = $content_type;
        $this->highlighed   = $highlighted;
        $this->data         = $data;
    }

    /**
     * Get a preview that highlights the search term, or null if there is no highlight.
     * (Either unspoorted, or the kind of search doesn't have a highlight).
     *
     * @return string
     */
    public function getHighlight()
    {
        return $this->highlighed;
    }

    /**
     * Get all result data, generally used with transformers to fetch a real object.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the type of result this is.
     *
     * @return string
     */
    public function getContentTypeName()
    {
        return $this->content_type;
    }

    /**
     * Get the result ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
