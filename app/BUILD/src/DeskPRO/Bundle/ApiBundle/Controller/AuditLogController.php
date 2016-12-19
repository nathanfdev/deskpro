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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AuditBundle\Log\AuditLogService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AuditLogController.
 *
 * @ApiModes("all")
 */
class AuditLogController extends BaseController
{
    /**
     * @Rest\Get("/audit_logs")
     * @Rest\View(serializerGroups={"list"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $storage = $this->get('audit_log.storage');
        $qb      = $storage->createQueryBuilder();
        $this->applyFilters($request, $qb);
        $adapter = $storage->getPaginationAdapter($qb);

        $pagination = new Pagerfanta($adapter);

        $pagination->setCurrentPage($request->query->getInt('page', 1));
        $pagination->setMaxPerPage($request->query->getInt('count', 10));

        return View::create($this->wrap($pagination));
    }

    /**
     * @Rest\Get("/audit_logs/{id}")
     * @Rest\View(serializerGroups={"list", "details"})
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $storage = $this->get('audit_log.storage');
        if (!$entity = $storage->find($id)) {
            throw new NotFoundHttpException();
        }

        return View::create($this->wrap($entity));
    }

    /**
     * @Rest\Post("/audit_logs/purge")
     *
     * @param Request $request
     *
     * @return View
     */
    public function purgeAction(Request $request)
    {
        switch ($request->request->get('period')) {
            case 'day':
                $period = AuditLogService::PERIOD_1_DAY;
                break;
            case 'week':
                $period = AuditLogService::PERIOD_1_WEEK;
                break;
            case 'month':
                $period = AuditLogService::PERIOD_1_MONTH;
                break;
            case '3_months':
                $period = AuditLogService::PERIOD_3_MONTHS;
                break;
            case '6_months':
                $period = AuditLogService::PERIOD_6_MONTHS;
                break;
            case 'year':
                $period = AuditLogService::PERIOD_1_YEAR;
                break;
            case 'all':
                $period = null;
                break;
            default:
                throw new BadRequestHttpException('Invalid period');
        }

        $service = $this->get('audit_log.service');
        $service->delete($period);
    }

    private function applyFilters(Request $request, $qb)
    {
        $storage = $this->get('audit_log.storage');
        $query   = $request->query->all();

        $filters   = [];
        $available = [
            'object_id',
            'object_type',
            'date_created_from',
            'date_created_to',
            'action',
            'performer_name',
            'performer_id',
            'apiKey',
        ];
        $filtersSet = array_intersect($available, array_keys($query));

        foreach ($filtersSet as $filterName) {
            $filters[$filterName] = $query[$filterName];
        }

        $storage->applyFilters($filters, $qb);
    }
}
