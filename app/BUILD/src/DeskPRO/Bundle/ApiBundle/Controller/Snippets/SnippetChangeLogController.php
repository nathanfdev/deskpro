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

namespace DeskPRO\Bundle\ApiBundle\Controller\Snippets;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\SnippetChangeLog;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SnippetsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/snippets_change_logs")
 * @Feature("new_snippets")
 * @ApiDoc(
 *     target="all",
 *     section="Snippets",
 *     output="DeskPRO\Bundle\AppBundle\Entity\SnippetChangeLog"
 * )
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="inline_sideloads", "pattern"="true|false", "description"="sideload entities"},
 *          {"name"="include", "pattern"="\w[,\w]", "description"="entities to sideload (ex: ticket_message)"},
 *          {"name"="snippet_id", "pattern"="\w[,\w]", "description"="Snippet Id"},
 *          {"name"="language_id", "pattern"="\w[,\w]", "description"="Language Id"},
 *          {"name"="type", "pattern"="\w[,\w]", "description"="Type"},
 *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
 *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
 *          {"name"="limit", "pattern"="\d", "description"="Max number of resources to return", "dataType"="integer"},
 *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
 *     }
 * )
 */
class SnippetChangeLogController extends CrudController
{
    public static $entity      = SnippetChangeLog::class;
    public static $listOrder   = 'desc';
    public static $exposeOnly  = ['list'];
    public static $sortOptions = [
        'date_created' => 'dateCreated',
    ];

    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $snippetId = $request->get('snippet_id');
        if ($snippetId) {
            $qb->andWhere("$alias.snippet = :snippet_id")
                ->setParameter('snippet_id', $snippetId);
        }
        $langId = $request->get('language_id');
        if ($langId) {
            $qb->andWhere("$alias.language = :lang_id")
                ->setParameter('lang_id', $langId);
        }
        $type = $request->get('type');
        if ($type) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq("$alias.type", ':type'),
                $qb->expr()->isNull("$alias.type")
            ))
                ->setParameter('type', $type);
        }
    }
}
