<?php

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
