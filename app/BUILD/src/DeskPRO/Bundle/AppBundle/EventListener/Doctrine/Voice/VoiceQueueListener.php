<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twilio\Exceptions\TwilioException;

/**
 * Class VoiceQueueListener.
 */
class VoiceQueueListener
{
    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param TwilioAdapter   $twilioAdapter
     * @param EntityManager   $em
     * @param RouterInterface $router
     */
    public function __construct(TwilioAdapter $twilioAdapter, EntityManager $em, RouterInterface $router)
    {
        $this->twilioAdapter = $twilioAdapter;
        $this->em            = $em;
        $this->router        = $router;
    }

    /**
     * @ORM\PrePersist()
     *
     * @param VoiceQueue $queue
     *
     * @throws TwilioException
     */
    public function onCreate(VoiceQueue $queue)
    {
        $taskQueue = $this->twilioAdapter->createTaskQueue($queue);
        if (!$taskQueue) {
            throw new TwilioException('Unable to create Twilio task queue');
        }

        $queue->setTaskQueueSid($taskQueue->sid);
    }

    /**
     * @ORM\PreUpdate()
     *
     * @param VoiceQueue         $queue
     * @param PreUpdateEventArgs $args
     */
    public function onUpdate(VoiceQueue $queue, PreUpdateEventArgs $args)
    {
        if ($args->hasChangedField('name') || $args->hasChangedField('routingModel')) {
            $this->twilioAdapter->updateTaskQueue($queue);
        }

        $agents = $queue->getAgents();
        if ($agents instanceof PersistentCollection) {
            $this->updateWorkers($agents->getInsertDiff());
            $this->updateWorkers($agents->getDeleteDiff());
        }
    }

    /**
     * @ORM\PreRemove()
     *
     * @param VoiceQueue $queue
     */
    public function onRemove(VoiceQueue $queue)
    {
        $account = $queue->getAccount();
        if (!$account) {
            return;
        }

        $account->getQueues()->removeElement($queue);

        $this->twilioAdapter->createOrUpdateWorkflow($account, $this->getAssignmentUrl($account));
        $this->twilioAdapter->deleteTaskQueue($queue);
        $queue->setTaskQueueSid(null);
    }

    /**
     * @ORM\PostPersist()
     *
     * @param VoiceQueue $queue
     */
    public function setTargetWorkers(VoiceQueue $queue)
    {
        $account = $queue->getAccount();
        if (!$account) {
            return;
        }

        $this->twilioAdapter->updateTaskQueue($queue);
        $this->updateWorkers($queue->getAgents());
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     *
     * @param VoiceQueue $queue
     */
    public function updateWorkflow(VoiceQueue $queue)
    {
        $account = $queue->getAccount();
        if (!$account) {
            return;
        }

        $workflow = $this->twilioAdapter->createOrUpdateWorkflow($account, $this->getAssignmentUrl($account));
        if (!$workflow) {
            return;
        }

        if ($account->getQueueWorkflowSid() !== $workflow->sid) {
            $account->setQueueWorkflowSid($workflow->sid);
            $this->em->persist($account);
            $this->em->flush();
        }
    }

    /**
     * @param Person[] $agents
     */
    private function updateWorkers($agents)
    {
        foreach ($agents as $agent) {
            if ($agent->getAgentData() && $agent->getAgentData()->getVoiceWorkerSid()) {
                $this->twilioAdapter->updateWorker($this->getVoiceAccount(), $agent);
            }
        }
    }

    /**
     * @return VoiceAccount|null
     */
    private function getVoiceAccount()
    {
        return $this->em->getRepository(VoiceAccount::class)->getVoiceAccount();
    }

    /**
     * @param VoiceAccount $account
     *
     * @return string
     */
    private function getAssignmentUrl(VoiceAccount $account)
    {
        return $this->router->generate('twilio_assignment_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
