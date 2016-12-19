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

use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @ApiModes("all")
 */
class ResetHelpdeskController extends AbstractController implements ProtectedControllerInterface
{
    public static $types = [
        'users',
        'agents',
        'tickets',
        'organizations',
        'triggers',
        'filters',
        'templates',
        'escalations',
        'fields',
        'departments',
        'perms',
        'kb',
        'news',
        'downloads',
        'feedback',
        'labels',
        'snippets',
        'apps',
    ];

    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());

        return $multi;
    }

    public function runAction(Request $request)
    {
        if (!$this->container->get('templating.globals')->canResetHelpdesk()) {
            throw new BadRequestHttpException('Can\'t reset helpdesk');
        }

        $queue = $this->container->getJobQueue();
        if (!$data = json_decode($request->getContent(), 1)) {
            return $this->statusAction();
        }

        $status = $this->getStatus();
        $last   = null;

        foreach (self::$types as $v) {
            if (!@$data[$v]) {
                continue;
            }
            if ('waiting' === @$status[$v]) {
                continue;
            }

            $job = $queue->add('reset.'.$v, [
                'context_person_id' => $this->person['id'],
            ]);

            if ($last) {
                $job->depends_on_job = $last;
            }
            $last = $job;
        }

        $this->em->flush();

        return $this->statusAction();
    }

    public function statusAction()
    {
        return $this->createJsonResponse($this->getStatus());
    }

    protected function getStatus()
    {
        $res = ['waiting' => false];
        $rep = $this->em->getRepository('DeskPRO:Job');

        foreach (self::$types as $type) {
            if (!$jobs = $rep->findBy(['type' => 'reset.'.$type], ['date_created' => 'desc'], 1)) {
                continue;
            }
            $status = $jobs[0]['status'];
            if (in_array($status, ['rejected', 'aborted'])) {
                $status = 'error';
            }
            if (in_array($status, ['inserting', 'reserved', 'processing'])) {
                $status = 'waiting';
            }
            $res[$type] = $status;

            if ('waiting' === $status) {
                $res['waiting'] = true;
            }
        }

        return $res;
    }
}
