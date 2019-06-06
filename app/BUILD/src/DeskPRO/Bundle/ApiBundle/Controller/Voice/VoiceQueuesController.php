<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceQueueType;
use DeskPRO\Bundle\VoiceBundle\Model\AverageWaitingTime;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VoiceQueuesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_queues")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\VoiceQueue")
 * @ApiUserContext("admin", agent={"list", "get", "count", "toggleAgent", "averageWaitingTime"})
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceQueueType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\VoiceQueue"
 *      }
 *     }
 * )
 */
class VoiceQueuesController extends AbstractVoiceCrudController
{
    public static $entity       = VoiceQueue::class;
    public static $type         = VoiceQueueType::class;
    public static $listPaginate = false;

    /**
     * @ApiDoc(
     *     description="Toggle agent in voice queue list",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     parameters={
     *       {"name"="enabled", "description"="is enabled", "dataType"="boolean", "required"=false}
     *     }
     * )
     *
     * @Rest\Put("/{queue}/toggle_agent")
     *
     * @param VoiceQueue $queue
     * @param Request    $request
     *
     * @return View
     */
    public function toggleAgentAction(VoiceQueue $queue, Request $request)
    {
        $enabled = $request->request->get('enabled');

        /** @var Person $person */
        $person = $this->getUser();
        /** @var VoiceQueueAgent $voiceAgent */
        $voiceAgent = $queue->getAgents()->filter(function (VoiceQueueAgent $voiceAgent) use ($person) {
            return $voiceAgent->getAgent() === $person;
        })->first();

        if ($voiceAgent) {
            $voiceAgent->setIsEnabled($enabled);

            $em = $this->getManager();
            $em->persist($voiceAgent);
            $em->flush();
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Get average waiting time for each voice queue",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Get("/average_waiting_time")
     *
     * @return View
     */
    public function averageWaitingTimeAction()
    {
        $waitingUsers = [];

        /** @var VoiceQueue[] $voiceQueues */
        $voiceQueues = $this->getManager()->getRepository(VoiceQueue::class)->findAll();
        foreach ($voiceQueues as $voiceQueue) {
            $taskQueue = $this->get('dp.voice.task_router.storage')->getTaskQueue(VoiceWorkflow::getChannelName(), $voiceQueue->getId());
            if ($taskQueue) {
                $waitingUsers = $taskQueue->getAttribute('waiting_users');
                if ($waitingUsers) {
                    $waitingUsers[$voiceQueue->getId()] = new AverageWaitingTime($voiceQueue, $waitingUsers);
                }
            }
        }

        return new View($this->wrap($waitingUsers));
    }
}
