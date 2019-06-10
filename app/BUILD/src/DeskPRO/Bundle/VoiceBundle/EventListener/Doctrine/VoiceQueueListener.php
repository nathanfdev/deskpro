<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoiceQueueListener.
 */
class VoiceQueueListener
{
    /**
     * @internal
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     *
     * @param VoiceQueue         $voiceQueue
     * @param LifecycleEventArgs $args
     */
    public function checkDepartmentBrand(VoiceQueue $voiceQueue)
    {
        $queueDepartment = $voiceQueue->getDepartment();
        if ($queueDepartment) {
            $queueBrand = $voiceQueue->getBrand();
            if (!$queueBrand || ($queueBrand && !$queueDepartment->getBrands()->contains($queueBrand))) {
                $voiceQueue->setBrand($queueDepartment->getBrands()->first());
            }
        } else {
            $voiceQueue->setBrand(null);
        }
    }
}
