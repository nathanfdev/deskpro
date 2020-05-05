<?php

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\CustomFields\FieldDisplayArray;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicComment;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFeedback;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketParticipant;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use DeskPRO\Bundle\SendmailBundle\View\Model\EventCodeEmailBaseType;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalType;
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

    public function convertParameter($entity)
    {
        if (is_array($entity) || $entity instanceof \Traversable) {
            $result = [];
            foreach ($entity as $item) {
                $model = $this->convertParameter($item);
                if ($model instanceof Person) {
                    // TicketParticipant fix
                    $model = $this->convertParameter($model);
                }
                $result[] = $model;
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
            case Blob::class:
                $handler = $this->container->get('api_serializer.handler.blob');

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
            case CommunityTopic::class:
                $handler = $this->container->get('api_serializer.handler.community_topic');

                break;
            case CommunityTopicComment::class:
                $handler = $this->container->get('api_serializer.handler.community_topic_comment');

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
            case TicketFeedback::class:
                $handler = $this->container->get('api_serializer.handler.ticket_feedback');

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
            case TicketApproval::class:
                $handler = $this->container->get('api_serializer.handler.ticket_approval');

                break;
            case ApprovalResponse::class:
                $handler = $this->container->get('api_serializer.handler.approval_response');

                break;
            case \DateTime::class:
                /* @var \DateTime $entity */
                return $entity->format('c');
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
        $convertedArguments = [];
        foreach ($arguments as $argument) {
            $convertedArguments[] = $this->convertParameter($argument);
        }

        $reflection = new ReflectionClass($class);

        /** @var EmailBaseType $viewModel */
        $viewModel =  $reflection->newInstanceArgs($convertedArguments);

        if (in_array(EventCodeEmailBaseType::class, class_uses($viewModel)) || $viewModel instanceof TicketApprovalType) {
            $viewModel->setEmailSourceId(substr(sha1($this->container->getSetting('core.deskpro_uuid')), 0, 6));
            $viewModel->setEventCode();
        }

        return $viewModel;
    }

    /**
     * @param Ticket $ticket
     * @param bool   $forAgent
     *
     * @return array
     */
    protected function getTicketArguments($ticket, $forAgent = false)
    {
        $ticketLink = $this->objectRouter->getPortalUrl($ticket);

        $ticketPerson   = $ticket->getPerson();
        $ticketAgent    = $ticket->getAgent();
        $ticketMessages = $this->container->getEm()->getRepository(TicketMessage::class)->getTicketMessages(
            $ticket,
            [
                'with_notes'       => $forAgent,
                'with_attachments' => true,
                'limit'            => 15,
                'order'            => 'DESC',
            ]
        );
        $ticketFeedback = $this->container->getEm()->getRepository(TicketFeedback::class)->getFeedbackForTicket($ticket);

        return [$ticket, $ticketPerson, $ticketAgent, $ticketLink, $ticketMessages, $ticketFeedback];
    }

    /**
     * @param string $type
     * @param Ticket $ticket
     * @param Person $recipient
     * @param string $status
     * @param $mode
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function getTicketApprovalArguments($type, Ticket $ticket, Person $recipient, $status, $mode = null)
    {
        $approval = new TicketApproval();
        $approval
            ->setName('Sample Approval Title')
            ->setDescription('This is a sample approval description')
            ->setId(83)
            ->setTicket($ticket)
            ->setType(new ApprovalType())
            ->setCreatedBy($recipient)
            ->addApprover($recipient)
            ->setTemplate(new ApprovalTemplate())
        ;

        $approvalResponse = new ApprovalResponse();
        $approvalResponse
            ->setId(8)
            ->setApprover($recipient)
        ;

        switch ($status) {
            case AbstractBaseApproval::STATUS_APPROVED:
                $approval
                    ->setRequiredApprovals(1)
                    ->setRequiredRejections(1)
                ;
                $approvalResponse
                    ->setVote(ApprovalResponse::VOTE_APPROVE)
                    ->setMessage('I approve this')
                ;
                $approval->addResponse($approvalResponse);

                break;
            case AbstractBaseApproval::STATUS_REJECTED:
                $approval
                    ->setRequiredApprovals(1)
                    ->setRequiredRejections(1)
                ;
                $approvalResponse
                    ->setVote(ApprovalResponse::VOTE_REJECT)
                    ->setMessage('I reject this')
                ;
                $approval->addResponse($approvalResponse);

                break;
            case AbstractBaseApproval::STATUS_CANCELLED:
                $approval
                    ->setRequiredApprovals(1)
                    ->setRequiredRejections(1)
                    ->cancel($recipient)
                ;

                break;
            case AbstractBaseApproval::STATUS_PENDING:
                $approval
                    ->setRequiredApprovals(2)
                    ->setRequiredRejections(2)
                    ->addApprover($ticket->getPerson())
                ;

                if ('pending_approval' === $mode) {
                    $approvalResponse
                        ->setVote(ApprovalResponse::VOTE_APPROVE)
                        ->setMessage('I approve this')
                    ;
                    $approval->addResponse($approvalResponse);
                } elseif ('pending_rejection' === $mode) {
                    $approvalResponse
                        ->setVote(ApprovalResponse::VOTE_REJECT)
                        ->setMessage('I reject this')
                    ;
                    $approval->addResponse($approvalResponse);
                }

                break;
        }

        return [
            $type,
            $ticket,
            $approval,
            $recipient,
            true,
            false,
            'https://example.com/approve',
            'https://example.com/reject',
            $approvalResponse,
            $approval->getResponses()->toArray(),
        ];
    }
}
