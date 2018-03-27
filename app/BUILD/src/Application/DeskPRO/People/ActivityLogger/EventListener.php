<?php

namespace Application\DeskPRO\People\ActivityLogger;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\People\ActivityLogger\ActionType\NewCommentArticle;
use Application\DeskPRO\People\ActivityLogger\ActionType\NewCommentDownload;
use Application\DeskPRO\People\ActivityLogger\ActionType\NewCommentFeedback;
use Application\DeskPRO\People\ActivityLogger\ActionType\NewCommentNews;
use Application\DeskPRO\People\ActivityLogger\ActionType\NewTicket;
use Application\DeskPRO\People\ActivityLogger\ActionType\NewTicketReply;
use Application\DeskPRO\People\ActivityLogger\ActionType\Registered;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class EventListener implements EventSubscriber
{
    /** @var DeskproContainer */
    protected $container;

    /** @var \SplQueue */
    protected $queue;

    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
        $this->queue     = new \SplQueue();
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::postPersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        switch (true) {

            case $entity instanceof Ticket:
                /* @var $entity Ticket */
                if ($entity->person) {
                    $this->queue->enqueue(new NewTicket($entity->person, $entity));
                }
                break;

            case $entity instanceof TicketMessage && $entity->ticket['id']:
                /* @var $entity TicketMessage */
                if ($entity->person) {
                    $this->queue->enqueue(new NewTicketReply($entity->person, $entity));
                }
                break;

            case $entity instanceof ArticleComment:
                /* @var $entity CommentAbstract */
                if ($entity->person) {
                    $this->queue->enqueue(new NewCommentArticle($entity->person, $entity));
                }
                break;

            case $entity instanceof DownloadComment:
                /* @var $entity CommentAbstract */
                if ($entity->person) {
                    $this->queue->enqueue(new NewCommentDownload($entity->person, $entity));
                }
                break;

            case $entity instanceof NewsComment:
                /* @var $entity CommentAbstract */
                if ($entity->person) {
                    $this->queue->enqueue(new NewCommentNews($entity->person, $entity));
                }
                break;

            case $entity instanceof FeedbackComment:
                /* @var $entity CommentAbstract */
                if ($entity->person) {
                    $this->queue->enqueue(new NewCommentFeedback($entity->person, $entity));
                }
                break;

            case $entity instanceof Person:
                /* @var $entity Person */
                $this->queue->enqueue(new Registered($entity));
                break;
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        while (!$this->queue->isEmpty()) {
            $action = $this->queue->dequeue();
            $this->container->getPersonActivityLogger()->saveAction($action);
        }
    }
}
