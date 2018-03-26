<?php

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
