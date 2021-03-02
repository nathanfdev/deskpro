<?php

namespace Application\DeskPRO\NewSearch\SearchEngine\Result;

use Application\DeskPRO\Entity;

class ResultSet
{
    /**
     * @var int
     */
    private $total;

    /**
     * @var array
     */
    private $results;

    /**
     * @var array
     */
    private $objectIdentifier;

    /**
     * @param array $results
     * @param null $total
     * @param array $objectIdentifier
     */
    public function __construct($results = [], $total = null, $objectIdentifier = [])
    {
        $this->results          = $results;
        $this->objectIdentifier = $objectIdentifier;

        if ($total === null) {
            $this->total = count($results);
        } else {
            $this->total = $total;
        }
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return mixed
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * @return array
     */
    public function getTypedResults()
    {
        $res = [];

        foreach ($this->results as $r) {
            if ($r instanceof Entity\Article) {
                $type = 'article';
            } elseif ($r instanceof Entity\News) {
                $type = 'news';
            } elseif ($r instanceof Entity\Download) {
                $type = 'download';
            } elseif ($r instanceof Entity\CommunityTopic) {
                $type = 'community';
            } elseif ($r instanceof Entity\Topic) {
                $type = 'topic';
            } elseif ($r instanceof Entity\Ticket) {
                $type = 'ticket';
            } elseif ($r instanceof Entity\Person) {
                $type = 'person';
            } elseif ($r instanceof Entity\ChatConversation) {
                $type = 'chat_conversation';
            } else {
                $type = 'unknown';
            }

            $res[] = ['type' => $type, 'object' => $r];
        }

        return $res;
    }
}
