<?php

namespace DeskPRO\Bundle\ApiBundle\Request\ParamConverter;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAgentTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceAutoAttendantTarget;
use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\VoiceQueueTarget;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class VoiceTargetConverter.
 */
class VoiceTargetConverter implements ParamConverterInterface
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
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $targetId   = $request->attributes->get($configuration->getOptions()['targetId']);
        $targetType = $request->attributes->get($configuration->getOptions()['targetType']);

        if (!$targetId || !$targetType) {
            throw new \LogicException('Invalid voice target params');
        }

        $target = null;

        if ($targetType === AbstractVoiceTarget::TYPE_AGENT) {
            $agent = $this->em->getRepository(Person::class)->find($targetId);
            if ($agent) {
                $target = new VoiceAgentTarget();
                $target->setAgent($agent);
            }
        } elseif ($targetType === AbstractVoiceTarget::TYPE_QUEUE) {
            $queue = $this->em->getRepository(VoiceQueue::class)->find($targetId);
            if ($queue) {
                $target = new VoiceQueueTarget();
                $target->setQueue($queue);
            }
        } elseif ($targetType === AbstractVoiceTarget::TYPE_AUTO_ATTENDANT) {
            $autoAttendant = $this->em->getRepository(VoiceQueue::class)->find($targetId);
            if ($autoAttendant) {
                $target = new VoiceAutoAttendantTarget();
                $target->setAutoAttendant($autoAttendant);
            }
        }

        if (!$target) {
            throw new NotFoundHttpException('Voice target is not found');
        }

        $request->attributes->set($configuration->getName(), $target);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'voice_target';
    }
}
