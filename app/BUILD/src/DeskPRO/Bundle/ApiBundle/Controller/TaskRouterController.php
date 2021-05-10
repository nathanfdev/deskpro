<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\VoiceBundle\Model\PendingTasksCount;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskRouterController.
 *
 * @ApiModes("all")
 * @Rest\Route("/task_router")
 * @ApiDoc(target="all", section="Task Router")
 */
class TaskRouterController extends BaseController
{
    /**
     * @Rest\Post("/create_worker")
     *
     * @throws \Exception
     *
     * @return View
     */
    public function createWorkerAction()
    {
        /** @var Person $person */
        $person = $this->getUser();
        if (!$person->getAgentData()) {
            $agentData = new AgentData();
            $agentData->setAvailableStatus(AgentData::AVAILABLE_STATUS_IDLE);
            $agentData->setPerson($person);

            $this->getManager()->persist($agentData);
            $this->getManager()->flush();
        } else {
            $agentData = $person->getAgentData();
        }

        // create a voice worker for the agent
        $storage = $this->get('dp.voice.task_router.storage');
        if (!$storage->getWorkerByType('agent', $person->getId())) {
            $worker = new Worker();
            $worker->setType('agent');
            $worker->setTypeId($person->getId());

            if ($agentData->getAvailableStatus() === AgentData::AVAILABLE_STATUS_IDLE) {
                $worker->setActivity(Worker::ACTIVITY_IDLE);
            } else {
                $worker->setActivity(Worker::ACTIVITY_OFFLINE);
            }

            $storage->saveWorker($worker);
        }

        $this->get('dp.voice.task_router')->updateLastWorkerActivity('agent', $person->getId());

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiUserContext("open")
     * @Rest\Get("/evaluate")
     *
     * @return View
     */
    public function callRouterAction()
    {
        // evaluate task router
        $processed = $this->container->get('dp.voice.task_router')->evaluate();

        $voiceSettings = $this->get('voice_settings_resolver');

        $lastVoiceTaskTimestamp = $voiceSettings->getLastVoiceTaskTimestamp();
        $lastChatTaskTimestamp  = $voiceSettings->getLastChatTaskTimestamp();

        $pendingCounts = new PendingTasksCount(
            $this->get('dp.voice.task_router.storage')->getActiveTasks(),
            $processed,
            $voiceSettings->getFailedEvaluateAttempts(),
            $lastVoiceTaskTimestamp ? new \DateTime('@'.$lastVoiceTaskTimestamp) : null,
            $lastChatTaskTimestamp ? new \DateTime('@'.$lastChatTaskTimestamp) : null
        );

        $this->get('dp.voice.task_router.logger')->info(sprintf(
            '[TaskRouter] Pending counts, processed = %s, failed_attempts = %s, num_pending_voice_tasks = %s, num_pending_chat_tasks = %s, last_pending_voice_task = %s, last_pending_chat_task = %s',
            (int) $processed,
            $voiceSettings->getFailedEvaluateAttempts(),
            $pendingCounts->getNumPendingVoiceTasks(),
            $pendingCounts->getNumPendingChatTasks(),
            $pendingCounts->getLastPendingVoiceTask() ? $pendingCounts->getLastPendingVoiceTask()->format('c') : null,
            $pendingCounts->getLastPendingChatTask() ? $pendingCounts->getLastPendingChatTask()->format('c') : null
        ));

        return new View($this->wrap($pendingCounts));
    }

    /**
     * @Rest\Get("/logs")
     *
     * @return Response
     */
    public function logsAction()
    {
        $path    = $this->get('deskpro.app_env')->getUserLogsDir().'/task_router.log';
        $content = '';
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if (!$content) {
                $content = '';
            }
        }

        return new Response($content);
    }
}
