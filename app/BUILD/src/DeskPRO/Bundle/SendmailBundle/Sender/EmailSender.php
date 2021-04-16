<?php

namespace DeskPRO\Bundle\SendmailBundle\Sender;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\EmailBundle\SwiftMailer\Mailer;
use Application\EmailBundle\SwiftMailer\Message\Message;
use Application\EmailBundle\SwiftMailer\Transport\StorageTransportInterface;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\PortalBundle\Model\EmailTo;
use DeskPRO\Bundle\SendmailBundle\Render\EmailRenderer;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentErrorUnknownFrom;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentTicketForward;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentWelcome;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentWelcomeUsersource;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use DeskPRO\Bundle\SendmailBundle\View\Model\EventCodeEmailBaseType;
use Doctrine\ORM\EntityManager;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailSender
{
    /**
     * @var EmailRenderer
     */
    private $renderer;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * EmailSender constructor.
     *
     * @param EmailRenderer      $emailRenderer
     * @param Mailer             $mailer
     * @param EntityManager      $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(
        EmailRenderer $emailRenderer,
        Mailer $mailer,
        EntityManager $entityManager,
        ContainerInterface $container
    ) {
        $this->renderer      = $emailRenderer;
        $this->mailer        = $mailer;
        $this->entityManager = $entityManager;
        $this->container     = $container;

        $this->optionsResolver = new OptionsResolver();
        $this->configureOptions();
    }

    /**
     * @return EmailRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @return Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param EmailBaseType $model
     * @param $args
     * @param Message $message
     *
     * @throws \Exception
     *
     * @return Message
     */
    public function prepareMessage(EmailBaseType $model, $args, $message = null)
    {
        if (!$message) {
            $message = $this->getMailer()->createMessage();
        }

        /** @var PersonRepository $personRepository */
        $personRepository = $this->getEntityManager()->getRepository(Person::class);

        $options = $this->optionsResolver->resolve($args);

        if (is_string($options['to'])) {
            $recipient = $personRepository->findOneByEmail($options['to']);
        } elseif (is_a($options['to'], EmailTo::class)) {
            /** @var EmailTo $emailTo */
            $emailTo = $options['to'];
            if ($emailTo->getPerson()) {
                $recipient = $emailTo->getPerson();
            } else {
                $recipient = $personRepository->findOneByEmail($emailTo->getEmailAddress());
            }
        } elseif (is_a($options['to'], Person::class)) {
            $recipient = $options['to'];
        } elseif (!$message->getTo()) {
            throw new \Exception('Missing required "to" argument');
        }
        if (!empty($recipient) && is_a($recipient, Person::class)) {
            $skipCheck = [
                // Agent email sent to an unknown email address for agent ticket replies
                // ("your reply was not accepted because it was sent from an unknown address")
                AgentErrorUnknownFrom::class => 1,
                // If you created a new agent after calling the AgentDataServer,
                // then the repository wont contain the new agent when sending this welcome.
                AgentWelcome::class => 1,
                // When an agent is created via a usersource, they are not yet in the agent repository
                AgentWelcomeUsersource::class => 1,
                // When you send a forward email
                AgentTicketForward::class => 1,
            ];
            $class = get_class($model);
            if (strpos($class, '\Agent') !== false && !isset($skipCheck[$class])) {
                if (!$recipient->isAgent()) {
                    // Not an agent
                    // - Generate error log warning
                    // - Send in error report to us
                    // - Blank out email. We need to send a blank email because
                    // there is no way to "stop" at this late stage (it's too "late" by the time this code gets run)
                    // and if we were to throw an exception, it would cause rollbacks to happen.
                    // - TO DO: Can implement custom swiftmailer classes to allow cancelling of messages so the blank
                    // email isn't sent.

                    $contact = "{$recipient->getName()} <{$recipient->getPrimaryEmailAddress()}>";

                    $errorMessage = 'Agent email being sent to a non-agent. ';
                    $errorMessage .= "New Email Template: {$model->getTemplate()}, Person: {$contact}";

                    $e = new \InvalidArgumentException($errorMessage);
                    SystemErrorHandler::logException($e, true);

                    $message->setTemplate(null);
                    $message->setTemplateEngine(null);
                    $message->setToPerson(null);
                    $message->setBody('');
                    $message->setSubject('');
                    $message->getHeaders()->addTextHeader(
                        'X-DeskPRO-Error',
                        $errorMessage
                    );

                    return $message;
                }
            }
        }

        $serializationContext = new SideloadSerializationContext();
        $serializationContext->setInlineSideloads(true);
        if (!empty($recipient)) {
            $person = $this->container->get('api_serializer.handler.person')
                ->createModel($recipient, $serializationContext);
            $model->setRecipient($person);
            $message->setToPerson($recipient, $options['override_email']);
        } elseif (is_a($options['to'], EmailTo::class)) {
            $emailTo = $options['to'];
            $message->setTo($emailTo->getEmailAddress(), $emailTo->getName());
            $tempPerson = new Person();
            $tempPerson->setName($emailTo->getName());
            $email = new PersonEmail();
            $email->setEmail($emailTo->getEmailAddress());
            $tempPerson->setPrimaryEmail($email);
            $person = $this->container->get('api_serializer.handler.person')
                ->createModel($tempPerson, $serializationContext);
            $model->setRecipient($person);
        } elseif ($options['to']) {
            $message->setTo($options['to']);
        }

        if (null === $recipient) {
            $model->setRecipient(null);
        }
        $template = isset($options['template']) ? $options['template'] : $model->getTemplate();

        $language = $options['language'];
        if (!$language) {
            if (!empty($recipient)) {
                $language = $recipient->getLanguage();
            }
        }
        if ($language instanceof Language) {
            $emailCode = $this->getContainer()->get('language_manager')->callWithLanguage($language, function () use ($template, $model) {
                return $this->getRenderer()->render($template, $model);
            });
        } else {
            $emailCode = $this->getRenderer()->render($template, $model);
        }
        $message->setEncoder(\Swift_Encoding::getQpEncoding());
        foreach ($emailCode->getAttachments() as $blob) {
            $message->attachBlob($blob);
        }
        if (!empty($options['attachments'])) {
            foreach ($options['attachments'] as $attach) {
                $message->attach($attach);
            }
        }
        $body = $emailCode->getBody();
        $body = $message->replaceEmbeds($body);
        $body = $message->applyBodyFilter($body);
        $message->setBody($body, 'text/html');

        $subject = $emailCode->getSubject();

        // Try to clean up subject from whitespace
        $subject = \Orb\Util\Strings::removeEmptyLines($subject);
        $subject = \Orb\Util\Strings::trimLines($subject);
        $subject = str_replace(["\r\n", "\n"], ' ', $subject);
        $subject = trim($subject);
        $message->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));

        if (isset($options['headers'])) {
            foreach ($options['headers'] as $header) {
                $message->getHeaders()->addTextHeader($header['name'], $header['value']);
            }
        }

        if (in_array(EventCodeEmailBaseType::class, class_uses($model))) {
            /** @var EventCodeEmailBaseType $model */
            if ($model->getEmailSourceId()) {
                $message->getHeaders()->addTextHeader('X-Deskpro-EmailSourceId', $model->getEmailSourceId());
            }
            if ($model->getEventCodeType()) {
                $message->getHeaders()->addTextHeader('X-Deskpro-EmailEvent', $model->getEventCodeType());
            }
        }

        if (isset($options['from_account'])) {
            $message->setFrom($options['from_account']->getUseEmailAddress(), $options['from_name']);
        }

        if (!empty($options['Message-ID'])) {
            $message->getHeaders()->get('Message-ID')->setId($options['Message-ID']);
        }

        return $message;
    }

    /**
     * @param EmailBaseType $model
     * @param array         $args
     *
     * @throws \Exception
     */
    public function send(EmailBaseType $model, $args)
    {
        $message = $this->prepareMessage($model, $args);
        if ($this->mailer instanceof StorageTransportInterface) {
            $id = $this->mailer->queueMessage($message);
        } else {
            $this->mailer->send($message);
            $id = null;
        }

        return $id;
    }

    private function configureOptions()
    {
        $this->optionsResolver->setDefined([
            'template',
            'attachments',
            'headers',
            'from_account',
            'Message-ID',
            'language',
        ]);
        $this->optionsResolver->setDefaults([
            'to'             => null,
            'from_name'      => null,
            'language'       => null,
            'override_email' => null,
        ]);
        $this->optionsResolver->setAllowedTypes('attachments', 'array');
        $this->optionsResolver->setAllowedTypes('headers', 'array');
        $this->optionsResolver->setAllowedTypes('language', [Language::class, 'null']);
    }
}
