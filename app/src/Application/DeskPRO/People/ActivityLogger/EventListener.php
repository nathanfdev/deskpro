<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

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
	/** @var  DeskproContainer */
	protected $container;

	/** @var \SplQueue */
	protected $queue;

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
		$this->queue = new \SplQueue();
	}

	public function getSubscribedEvents()
	{
		return array(
			Events::prePersist,
			Events::postPersist,
		);
	}

	public function prePersist(LifecycleEventArgs $args)
	{
		$entity = $args->getEntity();
		switch (true) {

			case ($entity instanceof Ticket):
				/** @var $entity Ticket */
				$this->queue->enqueue(new NewTicket($entity->person, $entity));
				break;

			case ($entity instanceof TicketMessage && $entity->ticket['id']):
				/** @var $entity TicketMessage */
				$this->queue->enqueue(new NewTicketReply($entity->person, $entity));
				break;

			case ($entity instanceof ArticleComment):
				/** @var $entity CommentAbstract */
				$this->queue->enqueue(new NewCommentArticle($entity->person, $entity));
				break;

			case ($entity instanceof DownloadComment):
				/** @var $entity CommentAbstract */
				$this->queue->enqueue(new NewCommentDownload($entity->person, $entity));
				break;

			case ($entity instanceof NewsComment):
				/** @var $entity CommentAbstract */
				$this->queue->enqueue(new NewCommentNews($entity->person, $entity));
				break;

			case ($entity instanceof FeedbackComment):
				/** @var $entity CommentAbstract */
				$this->queue->enqueue(new NewCommentFeedback($entity->person, $entity));
				break;

			case ($entity instanceof Person):
				/** @var $entity Person */
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