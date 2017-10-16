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
