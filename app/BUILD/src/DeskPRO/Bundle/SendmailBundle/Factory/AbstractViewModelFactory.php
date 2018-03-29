<?php

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\CustomFields\FieldDisplayArray;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractViewModelFactory
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ObjectRouter
     */
    protected $objectRouter;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(RouterInterface $router, ObjectRouter $objectRouter, ContainerInterface $container)
    {
        $this->router       = $router;
        $this->objectRouter = $objectRouter;
        $this->container    = $container;
    }

    protected function convertParameter($entity)
    {
        if (is_array($entity) || $entity instanceof \Traversable) {
            $result = [];
            foreach ($entity as $item) {
                $result[] = $this->convertParameter($item);
            }

            return $result;
        }
        if (!is_object($entity)) {
            return $entity;
        }
        $serializationContext = new SideloadSerializationContext();
        $serializationContext->setInlineSideloads(true);
        $className = str_replace('Proxies\__CG__\\', '', get_class($entity));
        switch ($className) {
            case Article::class:
                $handler = $this->container->get('api_serializer.handler.article');
                break;
            case ArticleComment::class:
                $handler = $this->container->get('api_serializer.handler.article_comment');
                break;
            case ChatConversation::class:
                $handler = $this->container->get('api_serializer.handler.chat');
                break;
            case ChatMessage::class:
                $handler = $this->container->get('api_serializer.handler.chat_message');
                break;
            case Download::class:
                $handler = $this->container->get('api_serializer.handler.download');
                break;
            case Feedback::class:
                $handler = $this->container->get('api_serializer.handler.feedback');
                break;
            case FeedbackComment::class:
                $handler = $this->container->get('api_serializer.handler.feedback_comment');
                break;
            case LayoutField::class:
                $handler = $this->container->get('api_serializer.handler.layout_field');
                break;
            case News::class:
                $handler = $this->container->get('api_serializer.handler.news');
                break;
            case Organization::class:
                $handler = $this->container->get('api_serializer.handler.organization');
                break;
            case Person::class:
            case PersonGuest::class:
                $handler = $this->container->get('api_serializer.handler.person');
                break;
            case Task::class:
                $handler = $this->container->get('api_serializer.handler.task');
                break;
            case Ticket::class:
                $handler = $this->container->get('api_serializer.handler.ticket');
                break;
            case TicketMessage::class:
                $handler = $this->container->get('api_serializer.handler.ticket_message');
                break;
            case TicketParticipant::class:
                $handler = $this->container->get('api_serializer.handler.ticket_participant');
                break;
            case Topic::class:
                $handler = $this->container->get('api_serializer.handler.topic');
                break;
            case TopicComment::class:
                $handler = $this->container->get('api_serializer.handler.topic_comment');
                break;
            case FieldDisplayArray::class:
                $handler = $this->container->get('api_serializer.handler.field_display_array');
                break;
            default:
                throw new \Exception('Unset handler for class '.$className);
                break;
        }

        return $handler->createModel($entity, $serializationContext);
    }

    /**
     * @param $class
     * @param array $arguments
     *
     * @throws \Exception
     *
     * @return EmailBaseType
     */
    protected function convertParameters($class, $arguments = [])
    {
        foreach ($arguments as &$argument) {
            $argument = $this->convertParameter($argument);
        }

        $reflection = new ReflectionClass($class);

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return array
     */
    protected function getTicketArguments($ticket)
    {
        $ticketLink = $this->objectRouter->getPortalUrl($ticket);

        $ticketPerson   = $ticket->getPerson();
        $ticketAgent    = $ticket->getAgent();
        $ticketMessages = iterator_to_array($ticket->getMessages());

        return [$ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages];
    }
}
