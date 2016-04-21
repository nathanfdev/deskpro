<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Comments;

use Application\DeskPRO\Entity\NewsComment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class NewsAllCommentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/news_comments")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\NewsComment")
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="status", "dataType"="string", "pattern"="visible|deleted|agent|validating", "description"="filter by status"},
 *          {"name"="is_reviewed", "dataType"="integer", "pattern"="1|0", "description"="filter by reviewed status"},
 *          {"name"="period_created", "dataType"="string", "pattern"="today|yesterday|this_week|this_month|last_month|this_year|ever", "description"="filter by period"},
 *          {"name"="article", "dataType"="integer", "pattern"="\d+", "description"="select comments for article with given id"},
 *          {"name"="download", "dataType"="integer", "pattern"="\d+", "description"="select comments for download with given id"},
 *          {"name"="news", "dataType"="integer", "pattern"="\d+", "description"="select comments for news with given id"}
 *     }
 * )
 * @ApiDoc(
 *     target="listAction",
 *     filters={
 *          {"name"="order_by", "dataType"="string", "pattern"="date_created|person", "description"="how to order comments"}
 *     }
 * )
 * @ApiDoc(
 *     target="countAction",
 *     filters={
 *          {"name"="group_by", "dataType"="string", "pattern"="news|status|period_created", "description"="how to groups comments"}
 *     }
 * )
 */
class NewsAllCommentsController extends AbstractAllCommentsController
{
    public static $contentType = 'news';
    public static $entity      = NewsComment::class;
}
