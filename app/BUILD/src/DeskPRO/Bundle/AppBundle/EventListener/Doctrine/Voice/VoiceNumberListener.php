<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\RestException;

/**
 * Class VoiceNumberListener.
 */
class VoiceNumberListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var array
     */
    private $pendingUpdates = [];

    /**
     * @var bool
     */
    private $processUpdates = false;

    /**
     * Constructor.
     *
     * @param ContainerInterface       $container
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ContainerInterface $container, EventDispatcherInterface $dispatcher)
    {
        $this->container  = $container;
        $this->dispatcher = $dispatcher;
    }

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

    /**
     * @internal
     *
     * @ORM\PrePersist()
     *
     * @param VoiceNumber        $number
     * @param LifecycleEventArgs $args
     */
    public function onCreate(VoiceNumber $number, LifecycleEventArgs $args)
    {
        $account = $number->getAccount();
        if (!$account) {
            return;
        }

        // link number to the DeskPRO app
        $this->container->get('twilio_adapter')->updateNumber($number, [
            'voiceApplicationSid' => $account->getTwimlAppSid(),
        ]);

        $this->updateDefaultCountryCodes($number, $args->getEntityManager());
        $this->notifyAgents($args->getEntityManager());
    }

    /**
     * @internal
     *
     * @ORM\PreUpdate()
     *
     * @param VoiceNumber        $number
     * @param PreUpdateEventArgs $args
     */
    public function updateWorker(VoiceNumber $number, PreUpdateEventArgs $args)
    {
        $notify = false;

        if ($args->hasChangedField('outboundCallsEnabled')) {
            $notify = true;
        }
        if ($args->hasChangedField('outboundCallsDefaultCountries')
            || $args->hasChangedField('outboundCallsDefault')
            || $args->hasChangedField('outboundCallsDefaultGlobal')
        ) {
            $this->updateDefaultCountryCodes($number, $args->getEntityManager());
            $notify = true;
        }

        if ($notify) {
            // send refresh alert
            $this->notifyAgents($args->getEntityManager());
        }
    }

    /**
     * @internal
     *
     * @ORM\PreRemove()
     *
     * @param VoiceNumber        $number
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
    public function onRemove(VoiceNumber $number, LifecycleEventArgs $args)
    {
        // unlink number from twilio
        try {
            $this->container->get('twilio_adapter')->updateNumber($number, [
                'voiceApplicationSid' => '',
            ]);
        } catch (RestException $e) {
            if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                return;
            }

            throw $e;
        }

        // send refresh alert
        $this->notifyAgents($args->getEntityManager());
    }

    /**
     * @internal
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->processUpdates = true;

        if ($this->pendingUpdates) {
            foreach ($this->pendingUpdates as $pendingUpdate) {
                $args->getEntityManager()->persist($pendingUpdate);
            }

            $this->pendingUpdates = [];
            $args->getEntityManager()->flush();
        }

        $this->processUpdates = false;
    }

    /**
     * @internal
     */
    public function onClear()
    {
        $this->pendingUpdates = [];
    }

    /**
     * Send agent alert to refresh the agent interface.
     *
     * @param EntityManager $em
     */
    private function notifyAgents(EntityManager $em)
    {
        $qb = $em->createQueryBuilder();
        $qb
            ->select('a, p')
            ->from(AgentData::class, 'a')
            ->join('a.person', 'p')
            ->where('a.isVoiceEnabled = 1')
        ;

        /** @var AgentData[] $agents */
        $agents = $qb->getQuery()->getResult();
        foreach ($agents as $agentData) {
            $this->dispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent('agent.ui.reload', [
                'type'        => 'admin',
                'person_id'   => 0,
                'person_name' => 'System',
                'target'      => $agentData->getPerson()->getId(),
            ]));
        }
    }

    /**
     * @param VoiceNumber   $number
     * @param EntityManager $em
     */
    private function updateDefaultCountryCodes(VoiceNumber $number, EntityManager $em)
    {
        $numberCountryCodes = $number->getOutboundCallsDefaultCountries();
        $numberCountryCodes = array_map('strtolower', $numberCountryCodes);

        $existNumbers = $em->getRepository(VoiceNumber::class)->findAll();
        foreach ($existNumbers as $existNumber) {
            if ($existNumber === $number) {
                continue;
            }

            $existNumberCountryCodes = $existNumber->getOutboundCallsDefaultCountries();
            $existNumberCountryCodes = array_map('strtolower', $existNumberCountryCodes);

            $changed = false;
            foreach ($existNumberCountryCodes as $num => $existNumberCountryCode) {
                if (in_array($existNumberCountryCode, $numberCountryCodes)) {
                    unset($existNumberCountryCodes[$num]);
                    $changed = true;
                }
            }

            if (!$this->processUpdates && $existNumber->isOutboundCallsDefaultGlobal() && $number->isOutboundCallsDefaultGlobal()) {
                $existNumber->setOutboundCallsDefaultGlobal(false);
                $changed = true;
            }

            if ($changed) {
                $existNumber->setOutboundCallsDefaultCountries(array_values($existNumberCountryCodes));
                $em->getUnitOfWork()->computeChangeSets();

                $this->pendingUpdates[spl_object_hash($existNumber)] = $existNumber;
            }
        }
    }
}
