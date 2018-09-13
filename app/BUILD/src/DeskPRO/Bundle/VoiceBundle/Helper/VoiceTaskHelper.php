<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use Doctrine\ORM\EntityManager;

/**
 * Class VoiceTaskHelper.
 */
class VoiceTaskHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Task $task
     *
     * @return Person|null
     */
    public function getWorkerAgent(Task $task)
    {
        $agentId = $task->getAttribute('agent');
        if (!$agentId) {
            return;
        }

        return $this->em->getRepository(Person::class)->find($agentId);
    }

    /**
     * @param Task $task
     *
     * @return VoiceQueue|null
     */
    public function getVoiceQueue(Task $task)
    {
        $queueId = $task->getAttribute('queue');
        if (!$queueId) {
            return;
        }

        return $this->em->getRepository(VoiceQueue::class)->find($queueId);
    }

    /**
     * @param Task $task
     *
     * @return VoicePhoneCall|null
     */
    public function getPhoneCall(Task $task)
    {
        $callId = $task->getAttribute('phone_call');
        if (!$callId) {
            return;
        }

        return $this->em->getRepository(VoicePhoneCall::class)->find($callId);
    }
}
