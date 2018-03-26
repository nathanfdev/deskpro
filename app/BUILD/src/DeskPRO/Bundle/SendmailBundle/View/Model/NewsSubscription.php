<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\News;
use JMS\Serializer\Annotation as JMS;

class NewsSubscription extends UserEmailBaseType
{
    public static $exampleData = ['class' => News::class, 'method' => 'findBy'];
    /**
     * The new news articles.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Content\News>")
     *
     * @var News[]
     */
    protected $newNews;

    /**
     * The updated news articles.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Content\News>")
     *
     * @var News[]
     */
    protected $updatedNews;

    /**
     * Link to unsubscribe to news articles.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $unsubscribeUrl;

    protected $templateFile = 'emails_user:news_subscription.html.twig';

    /**
     * NewsSubscription constructor.
     *
     * @param string $portalHome
     * @param string $unsubscribeUrl
     * @param News[] $newNews
     * @param News[] $updatedNews
     */
    public function __construct($portalHome, $unsubscribeUrl, $newNews, $updatedNews)
    {
        parent::__construct($portalHome);

        $this->newNews        = $newNews;
        $this->updatedNews    = $updatedNews;
        $this->unsubscribeUrl = $unsubscribeUrl;
    }
}
