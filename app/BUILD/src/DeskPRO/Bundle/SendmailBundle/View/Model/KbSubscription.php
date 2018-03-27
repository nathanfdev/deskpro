<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Article;
use JMS\Serializer\Annotation as JMS;

class KbSubscription extends UserEmailBaseType
{
    /**
     * The new articles.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Article>")
     *
     * @var Article[]
     */
    protected $newArticles;

    /**
     * The updated articles.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Article>")
     *
     * @var Article[]
     */
    protected $updatedArticles;

    /**
     * Link to unsubscribe to knowledge base articles.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $unsubscribeUrl;

    protected $templateFile = 'emails_user:kb_subscription.html.twig';

    /**
     * KbSubscription constructor.
     *
     * @param string    $portalHome
     * @param string    $unsubscribeUrl
     * @param Article[] $newArticles
     * @param Article[] $updatedArticles
     */
    public function __construct($portalHome, $unsubscribeUrl, $newArticles, $updatedArticles)
    {
        parent::__construct($portalHome);

        $this->newArticles     = $newArticles;
        $this->updatedArticles = $updatedArticles;
        $this->unsubscribeUrl  = $unsubscribeUrl;
    }
}
