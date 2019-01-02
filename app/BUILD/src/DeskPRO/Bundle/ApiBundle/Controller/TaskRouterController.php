<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
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
        if (!$this->get('dp.voice.task_router.storage')->getWorkerByType('agent', $person->getId())) {
            $worker = new Worker();
            $worker->setType('agent');
            $worker->setTypeId($person->getId());

            if ($agentData->getAvailableStatus() === AgentData::AVAILABLE_STATUS_IDLE) {
                $worker->setActivity(Worker::ACTIVITY_IDLE);
            } else {
                $worker->setActivity(Worker::ACTIVITY_OFFLINE);
            }

            $this->get('dp.voice.task_router.storage')->saveWorker($worker);
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }
}
