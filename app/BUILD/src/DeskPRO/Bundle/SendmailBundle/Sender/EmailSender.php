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
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use Doctrine\ORM\EntityManager;
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
        $serializationContext = new SideloadSerializationContext();
        $serializationContext->setInlineSideloads(true);
        if (!empty($recipient)) {
            $person = $this->container->get('api_serializer.handler.person')
                ->createModel($recipient, $serializationContext);
            $model->setRecipient($person);
            $message->setToPerson($recipient);
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
        $template = isset($options['template']) ? $options['template'] : $model->getTemplate();

        $language = $options['language'];
        if (!$language) {
            if (!empty($recipient)) {
                $language = $recipient->getLanguage();
            }
        }
        if ($language instanceof Language) {
            $emailCode = null;
            $this->getContainer()->get('deskpro.core.translate')->setTemporaryLanguage($language, function () use ($template, $model, &$emailCode) {
                $emailCode = $this->getRenderer()->render($template, $model);
            });
        } else {
            $emailCode = $this->getRenderer()->render($template, $model);
        }
        $message->setEncoder(\Swift_Encoding::getQpEncoding());
        $message->setBody($emailCode->getBody(), 'text/html');
        $message->setSubject(htmlspecialchars_decode($emailCode->getSubject(), ENT_QUOTES));
        foreach ($emailCode->getAttachments() as $blob) {
            $message->attachBlob($blob);
        }
        if (!empty($options['attachments'])) {
            foreach ($options['attachments'] as $attach) {
                $message->attach($attach);
            }
        }

        if (isset($options['headers'])) {
            foreach ($options['headers'] as $header) {
                $message->getHeaders()->addTextHeader($header['name'], $header['value']);
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
            'to'        => null,
            'from_name' => null,
            'language'  => null,
        ]);
        $this->optionsResolver->setAllowedTypes('attachments', 'array');
        $this->optionsResolver->setAllowedTypes('headers', 'array');
        $this->optionsResolver->setAllowedTypes('language', [Language::class, 'null']);
    }
}
