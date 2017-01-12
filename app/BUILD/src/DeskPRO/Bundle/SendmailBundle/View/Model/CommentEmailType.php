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

use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use JMS\Serializer\Annotation as JMS;

abstract class CommentEmailType extends EmailBaseType
{
    /**
     * The approved comment.
     *
     * @JMS\Type("Application\DeskPRO\Entity\CommentAbstract")
     *
     * @var CommentAbstract
     */
    protected $comment;

    /**
     * The commented content.
     *
     * @JMS\Type("Application\DeskPRO\Entity\ContentAbstract")
     *
     * @var ContentAbstract
     */
    protected $content;

    /**
     * The link to the content.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $contentLink;

    /**
     * CommentApproved constructor.
     *
     * @param ObjectRouter    $router
     * @param CommentAbstract $comment
     */
    public function __construct(ObjectRouter $router, $comment)
    {
        $this->comment = $comment;
        $this->content = $comment->getObject();

        $this->contentLink = $router->getPortalUrl($this->content);
    }
}
