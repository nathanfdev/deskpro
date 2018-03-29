<?php

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
 * @Rest\Route("/audit_logs")
 */
class AuditLogController extends BaseController
{
    /**
     * @Rest\Get("")
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
     * @Rest\Get("/{id}")
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
     * @Rest\Post("/purge")
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

    /**
     * @param Request $request
     * @param $qb
     */
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
