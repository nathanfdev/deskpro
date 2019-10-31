<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAgentTarget;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoicePermissionRemovalListener.
 */
class VoicePermissionRemovalListener implements EventSubscriber
{
    /**
     * @var Person[]
     */
    private $updateQueue = [];

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postFlush',
            'onClear',
        ];
    }

    public function onClear()
    {
        $this->updateQueue = [];
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->updateQueue) {
            $em = $args->getEntityManager();
            foreach ($this->updateQueue as $entity) {
                // remove from voice queues
                foreach ($entity->getVoiceQueues() as $voiceQueue) {
                    $voiceQueue->getQueue()->removeAgent($voiceQueue);
                    $em->persist($voiceQueue);
                }

                // remove from auto attendants
                $autoAttendants = $em->getRepository(VoiceAutoAttendant::class)->findAll();
                foreach ($autoAttendants as $autoAttendant) {
                    foreach ($autoAttendant->getDialNumbers() as $dialNumber) {
                        $target = $dialNumber->getTarget();
                        if ($target instanceof VoiceAgentTarget && $target->getAgent() === $entity) {
                            $autoAttendant->removeDialNumber($dialNumber);
                        }
                    }

                    $em->persist($autoAttendant);
                }
            }

            $this->updateQueue = [];
            $em->flush();
        }
    }

    /**
     * @ORM\PreUpdate()
     *
     * @param AgentData          $agentData
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(AgentData $agentData, PreUpdateEventArgs $args)
    {
        if ($args->hasChangedField('isVoiceEnabled') && !$agentData->isVoiceEnabled()) {
            $this->updateQueue[] = $agentData->getPerson();
            $agentData->setExtensionNumber(null);
        }
    }

    /**
     * @ORM\PreRemove()
     *
     * @param AgentData $agentData
     */
    public function preRemove(AgentData $agentData)
    {
        $this->updateQueue[] = $agentData->getPerson();

        $agentData->getPerson()->setAgentData(null);
        $agentData->setPerson(null);
    }
}
