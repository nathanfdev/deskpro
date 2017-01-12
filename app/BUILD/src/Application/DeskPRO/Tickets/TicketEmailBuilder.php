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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\CustomFields\TicketFieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Settings\Settings;
use Application\DeskPRO\TicketLayout\LayoutDisplay;
use Application\DeskPRO\TicketLayout\TicketLayoutManager;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;
use Swift_Mailer;

/**
 * Class TicketEmailBuilder.
 */
class TicketEmailBuilder
{
    /**
     * @var OptionsArray
     */
    private $options;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var Translate
     */
    private $translate;

    /**
     * @var EmailAccountManager
     */
    private $emailAccountManager;

    /**
     * @var TicketFieldManager
     */
    private $ticketFieldManager;

    /**
     * @var PersonFieldManager
     */
    private $personFieldManager;

    /**
     * @var TicketLayoutManager
     */
    private $ticketLayoutManager;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param EntityManager       $em
     * @param Settings            $settings
     * @param Swift_Mailer        $mailer
     * @param Translate           $translate
     * @param EmailAccountManager $emailAccountManager
     * @param TicketFieldManager  $ticketFieldManager
     * @param PersonFieldManager  $personFieldManager
     * @param TicketLayoutManager $ticketLayoutManager
     * @param BrandStack          $brandStack
     */
    private function __construct(
        EntityManager       $em,
        Settings            $settings,
        Swift_Mailer        $mailer,
        Translate           $translate,
        EmailAccountManager $emailAccountManager,
        TicketFieldManager  $ticketFieldManager,
        PersonFieldManager  $personFieldManager,
        TicketLayoutManager $ticketLayoutManager,
        BrandStack          $brandStack
    ) {
        $this->em                  = $em;
        $this->settings            = $settings;
        $this->mailer              = $mailer;
        $this->translate           = $translate;
        $this->emailAccountManager = $emailAccountManager;
        $this->ticketFieldManager  = $ticketFieldManager;
        $this->personFieldManager  = $personFieldManager;
        $this->ticketLayoutManager = $ticketLayoutManager;
        $this->brandStack          = $brandStack;
        $this->options             = new OptionsArray();

        $this->options->set('mailer', $mailer);
        $this->options->set('email_accounts', $emailAccountManager);
        $this->options->set('translate', $translate);
        $this->options->set('brand_stack', $brandStack);
    }

    /**
     * @param DeskproContainer $container
     *
     * @return TicketEmailBuilder
     */
    public static function createFromContainer(DeskproContainer $container)
    {
        $builder = new self(
            $container->getEm(),
            $container->getSettingsHandler(),
            $container->getMailer(),
            $container->getTranslator(),
            $container->getEmailAccountManager(),
            $container->getTicketFieldManager(),
            $container->getPersonFieldManager(),
            $container->getTicketLayoutManager(),
            $container->getBrandStack()
        );

        return $builder;
    }

    /**
     * @return TicketEmail
     */
    public function buildTicketEmail()
    {
        return new TicketEmail($this->options->all());
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketEmailBuilder
     */
    public function setTicket(Ticket $ticket)
    {
        $this->options->set('ticket', $ticket);

        return $this;
    }

    /**
     * @param Person $person
     *
     * @return TicketEmailBuilder
     */
    public function setToPerson(Person $person)
    {
        $this->options->set('to_person', $person);

        return $this;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setToPersonEmail($email)
    {
        $this->options->set('to_person_email', $email);

        return $this;
    }

    /**
     * @param Person[] $people
     *
     * @return $this
     */
    public function setToPeople(array $people)
    {
        throw new \RuntimeException();
    }

    /**
     * What type of user the email is intended for. This is a safety feature.
     * For examlpe, if it's an agent email but the user isn't an agent, we can catch
     * an error.
     *
     * @return TicketEmailBuilder
     */
    public function setUserMode()
    {
        $this->options->set('user_mode', 'user');

        return $this;
    }

    /**
     * @see setUserMode
     *
     * @return TicketEmailBuilder
     */
    public function setAgentMode()
    {
        $this->options->set('user_mode', 'agent');

        return $this;
    }

    /**
     * @param string $template_name
     *
     * @return TicketEmailBuilder
     */
    public function setTemplateName($template_name)
    {
        $this->options->set('template_name', $template_name);

        return $this;
    }

    /**
     * @param $from_name
     *
     * @return TicketEmailBuilder
     */
    public function setFromName($from_name)
    {
        $this->options->set('from_name', $from_name);

        return $this;
    }

    /**
     * @param EmailAccount $account
     *
     * @return TicketEmailBuilder
     */
    public function setFromEmailAccount(EmailAccount $account = null)
    {
        $this->options->set('from_email_account', $account);

        return $this;
    }

    /**
     * On user emails, this will CC in other user participants on the email.
     *
     * @return TicketEmailBuilder
     */
    public function enableUserCc()
    {
        $this->options->set('cc_users', true);

        return $this;
    }

    /**
     * @see enableUserCc
     *
     * @return TicketEmailBuilder
     */
    public function disableUserCc()
    {
        $this->options->set('cc_users', false);

        return $this;
    }

    /**
     * If this is an automatic email (e.g., auto-reply), then this will add 'auto' headers
     * to the email. These special headers prevent other automated systems from sending their
     * own auto-replies.
     *
     * @return TicketEmailBuilder
     */
    public function setIsAuto()
    {
        $this->options->set('is_auto', true);

        return $this;
    }

    /**
     * @see setIsAuto
     *
     * @return TicketEmailBuilder
     */
    public function setIsNotAuto()
    {
        $this->options->set('is_auto', true);

        return $this;
    }

    /**
     * Sets the maximum size of attachments that will be sent with the message.
     *
     * @param int $size
     *
     * @return TicketEmailBuilder
     */
    public function setMaxAttachSize($size)
    {
        $this->options->set('max_attach_size', (int) $size);

        return $this;
    }

    /**
     * @param Logger $logger
     *
     * @return TicketEmailBuilder
     */
    public function setLogger(Logger $logger)
    {
        $this->options->set('logger', $logger);

        return $this;
    }

    /**
     * @param array $headers
     *
     * @return TicketEmailBuilder
     */
    public function setHeaders($headers = [])
    {
        $this->options->set('headers', $headers);

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options->all();
    }

    /**
     * @param bool $forAgent
     *
     * @return array
     */
    public function getCommonVars($forAgent)
    {
        /** @var Ticket $ticket */
        $ticket        = $this->options->get('ticket');
        $userMode      = $this->options->get('user_mode');
        $isAuto        = $this->options->get('is_auto');
        $logger        = $this->options->get('logger');
        $maxAttachSize = $this->options->get('max_attach_size', 0);

        if ($ticket->getBrand()) {
            $this->brandStack->push($ticket->getBrand());
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketMessage $messageRepo */
        $messageRepo = $this->em->getRepository(TicketMessage::class);
        $messages    = $messageRepo->getTicketMessages(
            $ticket,
            [
                'with_notes'       => $forAgent,
                'with_attachments' => true,
                'limit'            => 15,
                'order'            => 'DESC',
            ]
        );

        $vars = [
            'ticket'   => $ticket,
            'is_auto'  => $isAuto,
            'messages' => $messages,
        ];

        // If we have a speciifc 'new message', then we need to trim
        // messages array down (which is ALL the latest messages, may be too many if we are re-sending)
        if (isset($vars['new_message'])) {
            $got    = false;
            $newArr = [];

            foreach (array_reverse($vars['messages']) as $m) {
                $newArr[] = $m;
                if ($vars['new_message'] === $m) {
                    $got = true;
                    break;
                }
            }

            if ($got) {
                $vars['messages'] = array_reverse($newArr);
            }
        }

        $department = $ticket->getDepartment();
        $layoutId   = $department ? $department->getId() : null;

        if ($userMode == TicketEmail::MODE_AGENT) {
            $layout = $this->ticketLayoutManager->getAgentLayouts()->getLayout($layoutId);
            $layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::VIEW_TICKET, $ticket);
        } else {
            $layout = $this->ticketLayoutManager->getUserLayouts()->getLayout($layoutId);
            $layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::VIEW_TICKET, $ticket);
        }

        if ($this->ticketFieldManager) {
            $customFields = $this->ticketFieldManager->getDisplayArrayForObject($ticket);
        } else {
            $customFields = [];
        }

        if ($this->personFieldManager) {
            $customUserFields = $this->personFieldManager->getDisplayArrayForObject($ticket->getPerson());
        } else {
            $customUserFields = [];
        }

        $vars['ticket_layout']      = $layout;
        $vars['custom_fields']      = $customFields;
        $vars['custom_user_fields'] = $customUserFields;

        $state = $ticket->getStateChangeRecorder();

        /** @var TicketAttachment[] $lastMessageAttachments */
        $lastMessageAttachments = [];
        if ($state->hasNewReply() && !$isAuto) {
            /** @var TicketMessage $lastMessage */
            $lastMessage = Arrays::getFirstItem($vars['messages']);

            // This check is because theoretically, the entire thread
            // could be agent notes (e.g., first message was turned into a note).
            // So if this is an email to a user, messages array will be empty
            // and this check will prevent warnings about trying to use a null $last_message.

            if ($lastMessage) {
                $logger->info(sprintf('[TicketEmail] New reply on #%d checking for attachments <= %d', $lastMessage->getId(), $maxAttachSize));

                $attachments = $lastMessage->getAttachments();
                if (count($attachments)) {
                    $logger->info(sprintf('[TicketEmail] Message has %d attachments', count($attachments)));
                    foreach ($attachments as $attachment) {
                        $blob = $attachment->getBlob();

                        if ($blob->getFilesize() <= $maxAttachSize) {
                            $logger->info(sprintf('[TicketEmail] Adding attachment %s', $blob->getFilename()));
                            $lastMessageAttachments[$attachment->getId()] = $attachment;
                        } else {
                            $logger->info(sprintf('[TicketEmail] Skipping attachment %s', $blob->getFilename()));
                        }
                    }
                } else {
                    $logger->info(sprintf('[TicketEmail] Message has no attachments'));
                }
            }

            if ($this->settings->get('core_tickets.enable_feedback') && $userMode == 'user' && $lastMessage && $lastMessage->getPerson()->isAgent() && !$lastMessage->isAgentNote()) {
                $vars['show_rating_link'] = true;
            }
        }

        if ($lastMessageAttachments) {
            $vars['attached_blobs'] = $lastMessageAttachments;
        }

        return $vars;
    }
}
