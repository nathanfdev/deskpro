<?php

namespace DeskPRO\Bundle\VoiceBundle\JobQueue\Processor;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\JobQueue\Processor\AbstractJobProcessor;
use Application\DeskPRO\Tickets\Actions\SendAgentEmail;
use Application\DeskPRO\Tickets\Actions\SendAgentNewEmail;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Features\FeaturesCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EmailWithTranscriptionProcessor.
 */
class EmailWithTranscriptionProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'voice_email_with_transcription_processor';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TicketManager
     */
    private $ticketManager;

    /**
     * @var JobQueue
     */
    private $jobQueue;

    /**
     * @var FeaturesCollection
     */
    private $features;

    /**
     * @var ContainerInterface|DeskproContainer
     */
    private $container;

    /**
     * Constructor.
     *
     * @param Connection         $connection
     * @param EntityManager      $em
     * @param TicketManager      $ticketManager
     * @param JobQueue           $jobQueue
     * @param FeaturesCollection $features
     * @param ContainerInterface $container
     */
    public function __construct(
        Connection         $connection,
        EntityManager      $em,
        TicketManager      $ticketManager,
        JobQueue           $jobQueue,
        FeaturesCollection $features,
        ContainerInterface $container
    ) {
        parent::__construct($connection);

        $this->em            = $em;
        $this->ticketManager = $ticketManager;
        $this->jobQueue      = $jobQueue;
        $this->features      = $features;
        $this->container     = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data, array $job)
    {
        // send ticket email here
        // when transcription is downloaded

        try {
            $ticketMessage = $this->em->getRepository(TicketMessage::class)->find($data['message_id']);
            if (!$ticketMessage) {
                throw new \RuntimeException("Ticket message #{$data['message_id']} not found");
            }

            /** @var TicketMessageVoicePhoneCall $messageAttribute */
            $messageAttribute = $ticketMessage->getAttribute('voice_phone_call');
            if (!$messageAttribute) {
                throw new \RuntimeException("Ticket message #{$data['message_id']} has no voice reference");
            }

            $phoneCall = $messageAttribute->getPhoneCall();
            if ($phoneCall->getFullRecording()->getTranscription() === null) {
                $this->jobQueue->retryByJobId($job['id'], new \DateTime('+5 minutes'));

                return;
            }

            $ticket = $messageAttribute->getMessage()->getTicket();
            if ($ticket->getMessages()->count() === 1) {
                $event = ExecutorContext::EVENT_NEW;
            } else {
                $event = ExecutorContext::EVENT_REPLY;
            }

            $person = $phoneCall->getPerson();
            if ($person && $person->isAgent()) {
                $context = $this->ticketManager->createAgentExecutorContext($person, $event, ExecutorContext::METHOD_PHONE);
            } else {
                $context = $this->ticketManager->createUserExecutorContext($person, $event, ExecutorContext::METHOD_PHONE);
            }

            $context->getVars()->set('run_transcription_processor', true);

            if ($this->features->hasFeature('email_templates')) {
                $action = new SendAgentNewEmail([
                    'agent_ids'    => $data['agent_ids'],
                    'template'     => $data['template'],
                    'from_name'    => $data['from_name'],
                    'from_account' => $data['from_account'],
                    'headers'      => $data['headers'],
                ]);
            } else {
                $action = new SendAgentEmail([
                    'agent_ids'    => $data['agent_ids'],
                    'template'     => $data['template'],
                    'from_name'    => $data['from_name'],
                    'from_account' => $data['from_account'],
                    'headers'      => $data['headers'],
                ]);
            }

            $action->setContainer($this->container);
            $action->applyAction($ticket, $context);

            $this->runSuccessHandler($job);
        } catch (\Exception $e) {
            $this->runExceptionHandler($job, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['message_id', 'agent_ids', 'template', 'from_name']);
        $resolver->setDefaults([
            'from_account' => null,
            'headers'      => null,
        ]);
    }
}
