<?php

namespace DeskPRO\Bundle\AppBundle\HitTrack\ViewCount;

/**
 * Simple value object containing view counts. The ViewProc will create one of these so the ViewProcUpdater can
 * save the counts to the db.
 */
class Views
{
    /**
     * @var array
     */
    private $map = [
        'article'  => [],
        'download' => [],
        'news'     => [],
        'feedback' => [],
    ];

    /**
     * @param int $article_id
     * @param int $num
     */
    public function registerArticleViews($article_id, $num = 1)
    {
        if (!isset($this->map['article'][$article_id])) {
            $this->map['article'][$article_id] = 0;
        }

        $this->map['article'][$article_id] += $num;
    }

    /**
     * @param int $download_id
     * @param int $num
     */
    public function registerDownloadViews($download_id, $num = 1)
    {
        if (!isset($this->map['download'][$download_id])) {
            $this->map['download'][$download_id] = 0;
        }

        $this->map['download'][$download_id] += $num;
    }

    /**
     * @param int $news_id
     * @param int $num
     */
    public function registerNewsViews($news_id, $num = 1)
    {
        if (!isset($this->map['news'][$news_id])) {
            $this->map['news'][$news_id] = 0;
        }

        $this->map['news'][$news_id] += $num;
    }

    /**
     * @param int $topic_id
     * @param int $num
     */
    public function registerCommunityViews($topic_id, $num = 1)
    {
        if (!isset($this->map['community'][$topic_id])) {
            $this->map['community'][$topic_id] = 0;
        }

        $this->map['community'][$topic_id] += $num;
    }

    /**
     * @return array
     */
    public function getArticleViews()
    {
        return $this->map['article'];
    }

    /**
     * @return array
     */
    public function getDownloadViews()
    {
        return $this->map['download'];
    }

    /**
     * @return array
     */
    public function getNewsViews()
    {
        return $this->map['news'];
    }

    /**
     * @return array
     */
    public function getCommunityViews()
    {
        return $this->map['community'];
    }
}
