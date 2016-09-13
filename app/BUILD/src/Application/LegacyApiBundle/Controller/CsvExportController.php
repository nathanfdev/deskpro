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
