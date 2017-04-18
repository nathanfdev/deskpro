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

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
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
            case News::class:
                $handler = $this->container->get('api_serializer.handler.news');
                break;
            case Person::class:
                $handler = $this->container->get('api_serializer.handler.person');
                break;
            case Ticket::class:
                $handler = $this->container->get('api_serializer.handler.ticket');
                break;
            case TicketMessage::class:
                $handler = $this->container->get('api_serializer.handler.ticket_message');
                break;
            case Topic::class:
                $handler = $this->container->get('api_serializer.handler.topic');
                break;
            case TopicComment::class:
                $handler = $this->container->get('api_serializer.handler.topic_comment');
                break;
            default:
                throw new \Exception('Unset handler for class '.$className);
                break;
        }

        return $handler->createModel($entity, $serializationContext);
    }

    protected function convertParameters($class, $arguments)
    {
        foreach ($arguments as &$argument) {
            $argument = $this->convertParameter($argument);
        }

        return new $class(...$arguments);
    }
}
