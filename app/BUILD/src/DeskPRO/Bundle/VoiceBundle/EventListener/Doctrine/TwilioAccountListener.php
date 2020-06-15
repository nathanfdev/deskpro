<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine;

use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twilio\Exceptions\TwilioException;

/**
 * Class TwilioAccountListener.
 */
class TwilioAccountListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TwilioVoiceAccount[]
     */
    private $updateQueue = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
     * @param TwilioVoiceAccount $account
     *
     * @throws TwilioException
     */
    public function postPersist(TwilioVoiceAccount $account)
    {
        $this->updateQueue[] = $account;
    }

    public function postFlush()
    {
        if ($this->updateQueue) {
            $configurator = $this->container->get('dp.voice.twiml_app_configurator');

            foreach ($this->updateQueue as $num => $account) {
                unset($this->updateQueue[$num]);
                $configurator->createOrUpdateTwimlApp($account);
            }
        }
    }

    public function onClear()
    {
        $this->updateQueue = [];
    }
}
