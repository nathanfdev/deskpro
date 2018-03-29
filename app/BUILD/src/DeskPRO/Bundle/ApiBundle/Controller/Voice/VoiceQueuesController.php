<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceQueueType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\RestException;

/**
 * Class VoiceQueuesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_queues")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\VoiceQueue")
 * @ApiUserContext("admin", agent={"list", "get", "count", "toggleAgent"})
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceQueueType",
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

            if ($person->getAgentData()->getVoiceWorkerSid()) {
                // disable async sync
                $this->get('event_listener.voice_queue')->setAllowSync(false);

                // force update twilio worker
                try {
                    $this->get('twilio_adapter')->updateAgentWorker($queue->getAccount(), $person);
                } catch (RestException $e) {
                    throw $this->createBadRequestException('Unable to sync Twilio account');
                }
            }
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }
}
