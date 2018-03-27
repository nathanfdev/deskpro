<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\EntityRepository\TaskQueue;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * Class CsvExportController.
 *
 * @ApiModes("all")
 */
class CsvExportController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    /**
     * @return JsonResponse
     */
    public function startAction()
    {
        /** @var TaskQueue $rep */
        $rep = $this->em->getRepository('DeskPRO:TaskQueue');

        if (!$rep->getTasksInGroup('data_export')) {
            $rep->enqueueTask(
                'Application\\DeskPRO\\TaskQueueJob\\CsvExport',
                [],
                'data_export'
            );
        }

        return $this->statusAction();
    }

    /**
     * @return JsonResponse
     */
    public function stopAction()
    {
        /** @var TaskQueue $rep */
        $rep = $this->em->getRepository('DeskPRO:TaskQueue');

        if ($tasks = $rep->getTasksInGroup('data_export', true)) {
            if ($task = end($tasks)) {
                $task['status']         = 'completed';
                $task['date_completed'] = new \DateTime();
                $this->em->flush();
            }
        }

        return $this->statusAction();
    }

    /**
     * @return JsonResponse
     */
    public function statusAction()
    {
        $res = ['status' => null];

        /** @var TaskQueue $rep */
        $rep = $this->em->getRepository('DeskPRO:TaskQueue');
        if ($tasks = $rep->getTasksInGroup('data_export', true)) {
            $task = end($tasks);

            if (new \DateTime('-1day') < $task['date_runnable']) {
                $data          = $task['task_data'];
                $res['status'] = $task['status'];
                $res['offset'] = (int) @$data['offset'];

                if ('comleted' === $task['status']) {
                    $res['file'] = @$data['file'];
                }
            }
        }

        return $this->createApiResponse($res);
    }

    /**
     * @return JsonResponse
     */
    public function listAction()
    {
        $datas = $this->em->getRepository('DeskPRO:TmpData')->getByName('csv_export.file', false);
        $ret   = [];

        foreach ($datas as $data) {
            /* @var $data TmpData */
            $ret[] = [
                'created'  => $data->date_created->format('Y-m-d H:i:s'),
                'code'     => $data->getCode(),
                'count'    => $data->getData('count'),
                'filename' => pathinfo($data->getData('file'), PATHINFO_BASENAME),
                'expire'   => $data->date_expire->format('Y-m-d H:i:s'),
            ];
        }

        return $this->createApiResponse($ret);
    }
}
