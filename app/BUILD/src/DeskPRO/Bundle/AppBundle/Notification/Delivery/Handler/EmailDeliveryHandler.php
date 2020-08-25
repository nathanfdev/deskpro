<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmailDeliveryHandler.
 */
class EmailDeliveryHandler extends AbstractDeliveryHandler
{
    const TYPE = 'notification.delivery.handler.email';

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param MessageInterface $message
     */
    public function schedule(MessageInterface $message)
    {
        $this->messages[] = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function deliver()
    {
        if (!empty($this->messages)) {
            foreach ($this->messages as $message) {
                switch ($message->getType()) {
                    case NewMessageEvent::EVENT_NAME:
                        $this->sendNewAgentChatMessage($message);

                        break;
                    default:
                        continue 2;
                }
            }
        }

        $this->messages = [];
    }

    /**
     * @param $id
     *
     * @return Person
     */
    private function getAgent($id)
    {
        return $this->container->get('doctrine.orm.default_entity_manager')->find(Person::class, $id);
    }

    /**
     * @param $id
     *
     * @return AgentChatMessage
     */
    private function getChatMessage($id)
    {
        return $this->container->get('doctrine.orm.default_entity_manager')->find(AgentChatMessage::class, $id);
    }

    private function sendNewAgentChatMessage($message)
    {
        $em          = $this->container->get('doctrine.orm.default_entity_manager');
        $agent       = $this->getAgent($message->getTarget());
        $chatMessage = $this->getChatMessage($message->getData()['data']['data']['id']);
        $session     = $em->getRepository(Session::class)->getSessionForPerson($agent, 30);

        if (!$session && $agent->getPref('agent_notif.chat_message.email')) {
            if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = App::$container->get('email.agent_viewmodel_factory')
                    ->createAgentNewImMessageModel($chatMessage);
                App::$container->get('email.email_sender')
                    ->send($viewModel, ['to' => $agent]);
            } else {
                $emailMessage = App::getMailer()->createMessage();
                $emailMessage->setTemplate('DeskPRO:emails_agent:new-agent-im-message.html.twig', [
                    'message' => $chatMessage,
                ]);
                $emailMessage->setToPerson($agent);
                App::getMailer()->send($emailMessage);
            }
        }
    }
}
