<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use JMS\Serializer\Annotation as JMS;

/**
 * Class CategoriesList.
 */
class CategoriesList
{
    /**
     * @JMS\Type("array<Application\DeskPRO\Entity\ArticleCategory>")
     * @JMS\Groups("list")
     *
     * @var array
     */
    protected $articles;

    /**
     * @JMS\Type("array<Application\DeskPRO\Entity\NewsCategory>")
     * @JMS\Groups("list")
     *
     * @var array
     */
    protected $news;

    /**
     * @JMS\Type("array<Application\DeskPRO\Entity\DownloadCategory>")
     * @JMS\Groups("list")
     * @JMS\SerializedName("downloads")
     *
     * @var array
     */
    protected $downloads;

    /**
     * CategoriesList constructor.
     *
     * @param $articles
     * @param $news
     * @param $downloads
     */
    public function __construct($articles, $news, $downloads)
    {
        $this->articles  = $articles;
        $this->news      = $news;
        $this->downloads = $downloads;
    }
}
