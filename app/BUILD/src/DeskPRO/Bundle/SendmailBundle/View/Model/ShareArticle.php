<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Article;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class ShareArticle extends EmailBaseType
{
    /**
     * The article.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Article")
     *
     * @var Article
     */
    protected $article;

    /**
     * The author.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $author;

    /**
     * A link to the article.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $articleLink;

    /**
     * Form message.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $message;

    /**
     * Form email address.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $email;

    /**
     * Form name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $name;

    protected $templateFile = 'emails_user:share_article.html.twig';

    public function __construct(Article $article, $articleLink, Person $author, $message, $email, $name)
    {
        $this->article     = $article;
        $this->author      = $author;
        $this->articleLink = $articleLink;
        $this->message     = $message;
        $this->email       = $email;
        $this->name        = $name;
    }
}
