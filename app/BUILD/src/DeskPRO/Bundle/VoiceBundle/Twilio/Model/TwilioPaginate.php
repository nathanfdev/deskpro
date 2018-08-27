<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio\Model;

use Twilio\Page;

/**
 * Class TwilioPageList.
 */
class TwilioPaginate
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
     * @param array $records
     * @param int   $pageNum
     * @param Page  $page
     */
    public function __construct(array $records, $pageNum, Page $page = null)
    {
        $this->records = $records;
        $this->pageNum = $pageNum;

        if ($page) {
            $this->hasNext = (bool) $page->getNextPageUrl();
            if (strpos($page->getNextPageUrl(), 'EOL') !== false) {
                $this->hasNext = false;
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
