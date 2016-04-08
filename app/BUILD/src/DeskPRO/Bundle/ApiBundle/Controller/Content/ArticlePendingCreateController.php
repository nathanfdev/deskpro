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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\ArticlePendingCreate;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\DataService\Content\ArticlePendingCreateCriteria;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ArticlePendingCreateController.
 *
 * @ApiModes("all")
 */
class ArticlePendingCreateController extends BaseController
{
    /**
     * Get count of articles that need to be created.
     *
     * @ApiDoc(
     *     section="Content",
     *     resourceDescription="Operations about pending articles",
     *     description="total count of articles should be created",
     *     statusCodes={
     *         200="Returned if request was succeeded",
     *         400="Returned if provided filters was wrong",
     *     },
     *    filters={
     *        {"name"="assigned_person", "dataType"="string|integer", "pattern"="me|\d+"}
     *    },
     *    output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     * @Rest\Get("/article_pending_create/counts", name="api_article_pending_create_counts")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getTotalCountAction(Request $request)
    {
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('COUNT(apc)')
            ->from(ArticlePendingCreate::class, 'apc');

        $params = $this->removeAdditionalParameters($request);
        $this->applyFilters($qb, $params);

        $total = $qb->getQuery()->getSingleScalarResult();
        $count = Count::fromValue($total);

        return View::create(
            $this->wrap($count),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *     section="Content",
     *     resourceDescription="Operations about pending articles",
     *     description="Get ArticlePendingCreate entities list",
     *     statusCodes={
     *         200="Returned if everything is ok",
     *         400="Returned if you filter set was wrong way formed",
     *         404="Specified person not found"
     *     },
     *    filters={
     *        {"name"="assigned_person", "dataType"="string|integer", "pattern"="me|\d+"}
     *    },
     * )
     * @Rest\Get("/article_pending_creates", name="api_article_pending_creates")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        /** @var \DeskPRO\Bundle\AppBundle\DataService\Content\ArticlePendingCreateDataService $dataService */
        $dataService = $this->get('data.apc');
        $params      = $this->removeAdditionalParameters($request);
        $params      = $dataService->normalizeAssigned($params, $this->getUser());

        try {
            $criteria = ArticlePendingCreateCriteria::fromParameters(
                $params,
                new OptionsResolver()
            );
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);
        $apc   = $dataService->selectAPC($criteria, $page, $count);

        return View::create(
            $this->wrap($apc),
            Response::HTTP_OK
        );
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $params
     */
    private function applyFilters(
        QueryBuilder $qb,
        array $params
    ) {
        // handle the only allowed filter "assigned_person"
        if (array_key_exists('assigned_person', $params)) {
            $assignee = $params['assigned_person'] === 'me'
                ? $this->getUser()
                : $this->findOr404(Person::class, $params['assigned_person']);

            $alias = $qb->getRootAliases()[0];
            $qb
                ->where($alias.'.assigned_person = :assignee')
                ->setParameters(compact('assignee'));
            unset($params['assigned_person']);
        }

        // throw Bad Request if there are any filers except "assigned_person"
        if (!empty($params)) {
            throw new BadRequestHttpException('Unknown parameters: '.implode(', ', array_keys($params)));
        }
    }
}
