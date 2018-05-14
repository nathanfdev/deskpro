<?php

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Mail\Message;
use Application\DeskPRO\Monolog\NullLogger;
use Application\DeskPRO\Tickets\Util as TicketUtil;
use Application\EmailBundle\SwiftMailer\Transport\StorageTransportInterface;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Monolog\Logger;
use Orb\Util\CheckedOptionsArray;

/**
 * Class TicketEmail.
 */
class TicketEmail
{
    const MODE_USER  = 'user';
    const MODE_AGENT = 'agent';

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\EmailAccountManager
     */
    private $emailAccounts;

    /**
     * @var \Application\DeskPRO\Translate\Translate
     */
    private $translate;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $toPerson;

    /**
     * @var string
     */
    private $toPersonEmail;

    /**
     * @var string
     */
    private $sentToEmail;

    /**
     * @var string
     */
    private $sentToName;

    /**
     * @var string[]
     */
    private $sentWithCcs;

    /**
     * @var int
     */
    private $sendmailSourceId;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    private $ticket;

    /**
     * @var string
     */
    private $userMode;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var string|null
     */
    private $fromName;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount
     */
    private $fromEmailAccount;

    /**
     * @var bool
     */
    private $isAuto;

    /**
     * @var bool
     */
    private $doCcUsers = true;

    /**
     * @var int
     */
    private $maxAttachSize = 0;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $headers;

    /**
     * Use TicketEmailBuilder to build the options array easier.
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException When there are invalid options
     */
    public function __construct(array $options)
    {
        $opt = new CheckedOptionsArray();
        $opt->addRequiredNames(
            'mailer',
            'email_accounts',
            'translate',
            'ticket',
            'to_person',
            'user_mode',
            'template_name',
            'brand_stack'
        );
        $opt->addValidNames(
            'from_name',
            'from_email_account',
            'to_person_email',
            'cc_users',
            'is_auto',
            'max_attach_size',
            'logger',
            'headers'
        );
        $opt->setAll($options);
        $opt->ensureRequired();

        $this->toPerson      = $opt->get('to_person');
        $this->toPersonEmail = $opt->get('to_person_email');
        $this->ticket        = $opt->get('ticket');
        $this->templateName  = $opt->get('template_name');
        $this->fromName      = $opt->get('from_name', '');

        $this->mailer           = $opt->get('mailer');
        $this->emailAccounts    = $opt->get('email_accounts');
        $this->translate        = $opt->get('translate');
        $this->brandStack       = $opt->get('brand_stack');
        $this->fromEmailAccount = $opt->get('from_email_account', null);

        $this->doCcUsers     = $opt->get('cc_users', false);
        $this->isAuto        = $opt->get('is_auto', false);
        $this->maxAttachSize = $opt->get('max_attach_size', 0);

        $this->userMode = $opt->get('user_mode');
        $this->headers  = $opt->get('headers', []);

        if ($opt->get('user_mode') == 'user') {
            $this->userMode = 'user';
        } elseif ($opt->get('user_mode') == 'agent') {
            $this->userMode = 'agent';
        }

        if ($opt->has('logger')) {
            $this->logger = $opt->get('logger');
        } else {
            $this->logger = new NullLogger();
        }

        if ($this->doCcUsers && $this->userMode == self::MODE_AGENT) {
            throw new \InvalidArgumentException('CC Users does not work on agent emails');
        }

        if ($this->userMode == self::MODE_AGENT && !$this->toPerson->is_agent) {
            throw new \InvalidArgumentException('Agent mode but person is not an agent');
        }
    }

    /**
     * @return string
     */
    public function getUserMode()
    {
        return $this->userMode;
    }

    /**
     * @return \Application\DeskPRO\Entity\EmailAccount
     */
    public function getFromEmailAccount()
    {
        return $this->fromEmailAccount;
    }

    /**
     * @return null|string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @return bool
     */
    public function getDoCcUsers()
    {
        return $this->doCcUsers;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @return \Application\DeskPRO\Entity\Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getToPerson()
    {
        return $this->toPerson;
    }

    /**
     * @param array $vars
     * @param bool  $doPrepare prevent message from actually rendering in case of use in SendmailBundle
     *
     * @throws \Exception
     * @throws null
     *
     * @return Message
     */
    public function prepareMailerMessage(array $vars = [], $doPrepare = true)
    {
        if ($this->toPerson && $this->toPerson->isAgent()) {
            $this->toPerson->loadHelper('Agent');
            $this->toPerson->loadHelper('AgentTeam');
        }

        $vars['person'] = $this->toPerson;

        if ($this->ticket->getBrand()) {
            $this->brandStack->push($this->ticket->getBrand());
        }

        // To user - use the selected email address on the ticket
        if ($this->toPersonEmail && $this->toPerson->hasEmailAddress($this->toPersonEmail)) {
            $toEmail = $this->toPersonEmail;
        } elseif ($this->userMode == self::MODE_USER) {
            if ($this->ticket->getTicketPersonEmail() && $this->ticket->getTicketPersonEmail()->getPerson() === $this->toPerson) {
                $toEmail = $this->ticket->getTicketPersonEmail()->getEmail();
                $this->logger->info(sprintf('[TicketEmail] to_email(1): %s', $toEmail));
            } elseif ($this->toPerson->getPrimaryEmail()) {
                $toEmail = $this->toPerson->getPrimaryEmail()->getEmail();
                $this->logger->info(sprintf('[TicketEmail] to_email(3): %s', $toEmail));
            } else {
                $this->logger->info(sprintf('[TicketEmail] to_email(4): no email'));
                throw new \RuntimeException('no email address');
            }

            // To agent
        } else {
            if (!$this->toPerson || !$this->toPerson->getPrimaryEmail()) {
                throw new \RuntimeException('No agent email to send to');
            }

            $toEmail = $this->toPerson->getPrimaryEmail()->getEmail();
        }

        $tac = null;
        if ($this->userMode == self::MODE_AGENT) {
            $tac = TicketUtil::getTacForPerson($this->ticket, $this->toPerson);
        }
        $vars['tac'] = $tac;

        $this->sentToName  = $this->toPerson->getDisplayName();
        $this->sentToEmail = $toEmail;
        $this->sentWithCcs = [];

        /** @var Message $message */
        $message = $this->mailer->createMessage();
        $this->logger->info(sprintf('[TicketEmail] To: %s -- Name: %s', $toEmail, $this->toPerson->getDisplayName()));
        $message->setTo([$toEmail => $this->toPerson->getDisplayName()]);
        $message->setContextId('ticket_gateway');

        if (isset($vars['attached_blobs'])) {
            /** @var TicketAttachment $attachment */
            foreach ($vars['attached_blobs'] as $attachment) {
                $message->attachBlob($attachment->getBlob(), $attachment->getBlob()->getDownloadUrl(true), $attachment->isInline());
            }
        }

        if ($doPrepare) {
            $message->setTemplate($this->templateName, $vars);
        }

        if ($this->userMode == self::MODE_USER && $this->doCcUsers) {
            foreach ($this->ticket->getUserParticipants() as $p) {
                if ($p->getPrimaryEmailAddress()) {
                    $ccEmail = $p->getPrimaryEmailAddress();
                    $ccName  = $p->getDisplayName();
                    if (!$ccEmail) {
                        continue;
                    }

                    if ($this->isAuto && $p->disable_autoresponses) {
                        $this->logger->info(sprintf('[TicketEmail] CC skipped because autoresponder: %s -- Name: %s', $ccEmail, $ccName));
                        continue;
                    }

                    $this->sentWithCcs[] = $ccEmail;

                    $message->addCc($ccEmail, $ccName);
                    $this->logger->info(sprintf('[TicketEmail] CC: %s -- Name: %s', $ccEmail, $ccName));
                }
            }
        }

        if (!$this->fromEmailAccount || !$this->fromEmailAccount->outgoing_account) {
            $this->fromEmailAccount = $this->emailAccounts->getAccountForTicket($this->ticket);
        }

        if (!$this->fromEmailAccount) {
            $this->logger->warning(sprintf('[TicketEmail] No from email to send mail from!'));
            throw new \RuntimeException('No from email to send mail from');
        }

        $fromEmail = $this->fromEmailAccount->getUseEmailAddress();
        $fromName  = $this->fromName;

        $this->logger->info(sprintf('[TicketEmail] From: %s -- Name: %s', $fromEmail, $fromName));
        $message->setFrom($fromEmail, $fromName);

        if ($tac) {
            $message->getHeaders()->get('Message-ID')->setId($tac->getUniqueEmailMessageId());
        } else {
            $message->getHeaders()->get('Message-ID')->setId($this->ticket->getUniqueEmailMessageId());
        }

        $message->getHeaders()->addIdHeader('References', $this->ticket->getEmailReferencesHeader());
        $message->getHeaders()->addIdHeader('In-Reply-To', $this->ticket->getEmailReferencesHeader());

        if (isset($vars['is_auto']) && $vars['is_auto']) {
            $message->getHeaders()->addTextHeader('X-DeskPRO-Auto', 'Yes');
            $message->setSuppressAutoreplies(true);
            $this->logger->info(sprintf('[TicketEmail] Is auto'));
        }

        if ($this->userMode == self::MODE_USER) {
            $lang = $this->ticket->getRealLanguage() ?: $this->toPerson->getLanguage();
        } else {
            $lang = $this->toPerson->getLanguage();
        }

        $this->logger->info(sprintf('[TicketEmail] Language: %s', $lang->getSystemName()));

        if ($doPrepare) {
            $start = microtime(true);
            $this->translate->setTemporaryLanguage($lang, function () use ($message) {
                $message->prepare();
            });
            $this->logger->info(sprintf('[TicketEmail] Prepare took %.3fs', microtime(true) - $start));
        }

        foreach ($this->headers as $header) {
            $message->getHeaders()->addTextHeader($header['name'], $header['value']);
        }

        /* If we added a brand in the stack we remove it */
        if ($this->ticket->getBrand()) {
            $this->brandStack->pop();
        }

        return $message;
    }

    /**
     * @param Message $message
     * @param Logger  $logger
     *
     * @return $int
     */
    public static function sendMailerMessage(Message $message, Logger $logger)
    {
        $mailer = App::$container->getMailer();
        $start  = microtime(true);

        if ($mailer instanceof StorageTransportInterface) {
            $id = $mailer->queueMessage($message);
            if ($id) {
                $logger->info(sprintf('[TicketEmail] SendmailSource ID #%d', $id));
            }
        } else {
            $mailer->send($message);
            $id = null;
        }

        $logger->info(sprintf('[TicketEmail] Send took %.3fs', microtime(true) - $start));

        return $id;
    }

    /**
     * @param array $vars
     */
    public function send(array $vars = [])
    {
        $message = $this->prepareMailerMessage($vars);

        if ($this->ticket->getBrand()) {
            $this->brandStack->push($this->ticket->getBrand());
        }

        $this->sendmailSourceId = self::sendMailerMessage($message, $this->logger);

        /* If we added a brand in the stack we remove it */
        if ($this->ticket->getBrand()) {
            $this->brandStack->pop();
        }
    }

    /**
     * @return string
     */
    public function getSentToEmail()
    {
        return $this->sentToEmail;
    }

    /**
     * @return string
     */
    public function getSentToName()
    {
        return $this->sentToName;
    }

    /**
     * @return \string[]
     */
    public function getSentWithCcs()
    {
        return $this->sentWithCcs;
    }

    /**
     * @return int
     */
    public function getSendmailSourceId()
    {
        return $this->sendmailSourceId;
    }
}
