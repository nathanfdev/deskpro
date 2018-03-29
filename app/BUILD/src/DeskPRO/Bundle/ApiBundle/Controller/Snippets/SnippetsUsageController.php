<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Snippets;

use Application\DeskPRO\Entity\TicketFeedback;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Entity\SnippetUseLog;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\AppBundle\Serializer\OffsetList;
use DeskPRO\Component\Pagerfanta\LimitedPager;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SnippetsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/snippets_use")
 * @Feature("new_snippets")
 * @ApiDoc(
 *     target="all",
 *     section="Snippets",
 *     output="DeskPRO\Bundle\AppBundle\Entity\SnippetUseLog"
 * )
 * @ApiDoc(
 *     target="listAction,countAction",
 *     filters={
 *          {"name"="inline_sideloads", "pattern"="true|false", "description"="sideload entities"},
 *          {"name"="include", "pattern"="\w[,\w]", "description"="entities to sideload (ex: ticket_message)"},
 *          {"name"="snippet_id", "pattern"="\w[,\w]", "description"="Snippet Id"},
 *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
 *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
 *          {"name"="limit", "pattern"="\d", "description"="Max number of resources to return", "dataType"="integer"},
 *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
 *     }
 * )
 */
class SnippetsUsageController extends CrudController
{
    public static $entity     = SnippetUseLog::class;
    public static $listOrder  = 'asc';
    public static $exposeOnly = ['list'];

    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $snippetId = $request->get('snippet_id');
        if ($snippetId) {
            $qb->andWhere("$alias.snippet = :snippet_id")
                ->setParameter('snippet_id', $snippetId);
        }
    }

    /**
     * @ApiDoc(
     *      description="Get collection of resources",
     *      tags={"CRUD"="#ffa500"},
     *      filters={
     *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
     *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
     *          {"name"="limit", "pattern"="\d", "description"="Max number of resources to return", "dataType"="integer"},
     *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
     *      },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      }
     * )
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW_LIST, $this->getPermissionGroupContext($request));

        $qb = $this->getManager()->createQueryBuilder();
        $qb->select('e');
        $qb->from(static::$entity, 'e');

        $this->applyListFilters($qb, 'e', $request);
        $this->applySorting($qb, 'e', $request);

        $ids = $request->get('ids');
        if ($ids) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $ids = array_map(function ($id) {
                return (int) $id;
            }, $ids);
            if (count($ids) > static::$listMaxResults) {
                throw $this->createBadRequestException('You can select maximum '.static::$listMaxResults.' entities');
            }

            $qb->andWhere('e.id IN (:ids)');
            $qb->setParameter('ids', $ids);
        }

        $limit = (int) $request->query->getInt('limit', static::$listLimit);
        if ($limit && $limit < 0) {
            throw $this->createBadRequestException('You must select a limit of at least 1');
        }

        // return QueryBuilder result or Pagerfanta depending on if pagination is enabled for the controller
        if (static::$listPaginate) {
            $page   = (int) $request->query->getInt('page', 1);
            $offset = (int) $request->query->getInt('offset');
            $count  = (int) $request->query->getInt('count', static::$listPerPage);

            if ($count > static::$listMaxResults) {
                throw $this->createBadRequestException('You can select maximum '.static::$listMaxResults.' entities');
            } elseif ($count <= 0) {
                throw $this->createBadRequestException('You must select at least 1 entity');
            }

            if ($offset) {
                $result = new OffsetList($qb, $count, $offset);
            } else {
                if ($limit) {
                    // adding limit to the initial qb will
                    // make the initial COUNT have a limit, which
                    // might speed it up a bit
                    $qb->setMaxResults($limit);

                    $pagerAdapter = new DoctrineORMAdapter($qb);
                    $pager        = new LimitedPager($pagerAdapter, $limit);
                } else {
                    $pagerAdapter = new DoctrineORMAdapter($qb);
                    $pager        = new Pagerfanta($pagerAdapter);
                }

                $pager->setMaxPerPage($count);
                $pager->setCurrentPage($page);

                $result = $pager;
            }
        } else {
            if ($limit) {
                $qb->setMaxResults($limit);
            }

            $result = $qb->getQuery()->getResult();
        }

        $ticketMessages = [];

        /** @var SnippetUseLog $use */
        foreach ($result as $use) {
            if ($use->getRating() !== null && $use->getType() === 'ticket') {
                $ticketMessages[] = $use->getTicketMessage()->getId();
            }
        }

        if (count($ticketMessages)) {
            $qb = $this->getManager()->createQueryBuilder();
            $qb->select('tf');
            $qb->from(TicketFeedback::class, 'tf');
            $qb->where('tf.ticket_message IN (:ticket_messages)');
            $qb->setParameter('ticket_messages', $ticketMessages);

            $ticketFeedbacks = $qb->getQuery()->getResult();

            /** @var TicketFeedback $ticketFeedback */
            foreach ($ticketFeedbacks as $ticketFeedback) {
                /** @var SnippetUseLog $use */
                foreach ($result as $use) {
                    if ($use->getRating() !== null && $use->getTicketMessage()->getId() === $ticketFeedback->getMessageId()) {
                        $use->setMessage($ticketFeedback->getMessage());
                    }
                }
            }
        }

        return View::create($this->wrap($result), Response::HTTP_OK);
    }
}
