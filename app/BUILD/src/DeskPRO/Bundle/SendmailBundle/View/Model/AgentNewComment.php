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

use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\CommentAbstract;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentAbstract;
use JMS\Serializer\Annotation as JMS;

class AgentNewComment extends EmailBaseType
{
    /**
     * The new comment.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\CommentAbstract")
     *
     * @var CommentAbstract
     */
    protected $comment;

    /**
     * The commented content.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentAbstract")
     *
     * @var ContentAbstract
     */
    protected $object;

    /**
     * The type to the content.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $objectType;

    /**
     * Link to agent interface.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $loginLink;

    protected $templateFile = 'emails_agent:new_comment.html.twig';

    /**
     * AgentNewComment constructor.
     *
     * @param CommentAbstract $comment
     * @param ContentAbstract $object
     * @param string          $objectType
     * @param string          $loginLink
     */
    public function __construct(CommentAbstract $comment, ContentAbstract $object, $objectType, $loginLink)
    {
        $this->comment    = $comment;
        $this->object     = $object;
        $this->objectType = $objectType;
        $this->loginLink  = $loginLink;
    }
}
