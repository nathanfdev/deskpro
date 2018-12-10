<?php

namespace DeskPRO\Bundle\VoiceBundle\Plivo\Model;

use Plivo\Resources\ResourceList;

/**
 * Class PlivoPaginate.
 */
class PlivoPaginate
{
    /**
     * @var array
     */
    protected $records;

    /**
     * @var int
     */
    protected $pageNum;

    /**
     * @var bool
     */
    protected $hasNext = false;

    /**
     * Constructor.
     *
     * @param array        $records
     * @param int          $pageNum
     * @param ResourceList $page
     */
    public function __construct(array $records, $pageNum, ResourceList $page = null)
    {
        $this->records = $records;
        $this->pageNum = $pageNum;

        if ($page) {
            $meta = $page->meta();
            if ($meta) {
                $this->hasNext = (bool) $meta['next'];
            }
        }
    }

    /**
     * @return array
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @return int
     */
    public function getPageNum()
    {
        return $this->pageNum;
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return $this->hasNext;
    }
}
