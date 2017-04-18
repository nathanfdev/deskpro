<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
