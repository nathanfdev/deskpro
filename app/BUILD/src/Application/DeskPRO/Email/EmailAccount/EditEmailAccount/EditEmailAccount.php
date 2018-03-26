<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount;

use Application\DeskPRO\Email\EmailAccount\IncomingAccount\NoopConfig;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\PhpMailConfig;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use DeskPRO\Component\Util\IpUtils;
use Doctrine\ORM\EntityManager;
use Orb\Validator\StringEmail;

class EditEmailAccount
{
    /**
     * @var string
     */
    public $address;

    /**
     * @var bool
     */
    public $is_enabled;

    /**
     * @var string
     */
    public $account_type;

    /**
     * @var string
     */
    public $other_addresses;

    /**
     * @var string
     */
    public $incoming_type;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig
     */
    public $in_gmail_account;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\Office365Config
     */
    public $in_office365_account;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\Pop3Config
     */
    public $in_pop3_account;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ImapConfig
     */
    public $in_imap_account;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\IncomingAccount\ExchangeConfig
     */
    public $in_exchange_account;

    /**
     * @var string
     */
    public $outgoing_type;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\OutgoingAccount\GmailConfig
     */
    public $out_gmail_account;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\OutgoingAccount\Office365Config
     */
    public $out_office365_account;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\OutgoingAccount\SmtpConfig
     */
    public $out_smtp_account;

    /**
     * @var \Application\DeskPRO\Email\EmailAccount\OutgoingAccount\ExchangeConfig
     */
    public $out_exchange_account;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount
     */
    private $account;

    public function __construct(EmailAccount $account)
    {
        $this->account         = $account;
        $this->is_enabled      = $account->is_enabled;
        $this->address         = $account->address;
        $this->account_type    = $account->account_type;
        $this->other_addresses = implode(', ', $account->other_addresses ?: []);
        $this->incoming_type   = $account->incoming_account ? $account->incoming_account->getType() : '';
        $this->outgoing_type   = $account->outgoing_account ? $account->outgoing_account->getType() : '';
    }

    /**
     * Applies form to the entities.
     *
     * @param bool $save_incoming True to save incoming account
     * @param bool $save_outgoing True to save outgoing account
     */
    public function apply($save_incoming = true, $save_outgoing = true)
    {
        $this->account->address      = strtolower($this->address);
        $this->account->is_enabled   = $this->is_enabled;
        $this->account->account_type = strtolower($this->account_type);

        if ($this->other_addresses) {
            $emails_arr = [];
            $emails     = explode(',', $this->other_addresses);
            foreach ($emails as $email) {
                $email = trim(strtolower($email));
                if (StringEmail::isValueValid($email)) {
                    $emails_arr[] = $email;
                }
            }

            $this->account->other_addresses = $emails_arr;
        } else {
            $this->account->other_addresses = null;
        }

        if ($save_incoming) {
            $this->account->incoming_account = $this->getIncomingAccountConfig();
            if (!$this->account->incoming_account) {
                $this->account->account_type = EmailAccount::TYPE_OUT;
            }
        }

        if ($save_outgoing) {
            $this->account->outgoing_account = $this->getOutgoingAccountConfig();
        }
    }

    /**
     * @param EntityManager $em
     * @param array         $trigger_actions
     *
     * @return TicketTrigger
     */
    public function saveTrigger(EntityManager $em, array $trigger_actions)
    {
        $trigger = $em->createQuery('
            SELECT trigger
            FROM DeskPRO:TicketTrigger trigger
            WHERE trigger.email_account = ?0
        ')->setParameters([$this->account])->getOneOrNullResult();

        if (!$trigger_actions || $this->account->account_type != 'tickets') {
            if ($trigger) {
                $em->remove($trigger);
                $em->flush();
            }

            return;
        }

        $lastEmailAccountTrigger = $em->createQuery('
            SELECT trigger
            FROM DeskPRO:TicketTrigger trigger
            WHERE trigger.email_account IS NOT NULL
            ORDER BY trigger.run_order desc
        ')->setMaxResults(1)->getResult();
        $lastEmailAccountTrigger = reset($lastEmailAccountTrigger) ?: null;

        if (!$trigger) {
            $trigger                = new TicketTrigger();
            $trigger->is_enabled    = (bool) $em->getConnection()->fetchColumn('SELECT id FROM ticket_triggers WHERE email_account_id IS NOT NULL AND is_enabled = 1 AND event_trigger = ?', [$trigger->event_trigger]);
            $trigger->email_account = $this->account;
            $trigger->event_trigger = 'newticket';
            $trigger->by_agent_mode = ['email'];
            $trigger->by_user_mode  = ['email'];
        }

        $actions = new TriggerActions();
        try {
            $actions->importFromArray(['actions' => $trigger_actions]);
        } catch (\Exception $e) {
        }

        $terms = new TriggerTerms();
        $terms->addTermFromArray([
            'type'    => 'CheckEmailAccount',
            'op'      => 'is',
            'options' => ['email_account_ids' => [$this->account->id]],
        ]);

        $trigger->title     = 'New Ticket';
        $trigger->run_order = $lastEmailAccountTrigger ? $lastEmailAccountTrigger->run_order : -100;
        $trigger->setActions($actions);
        $trigger->terms = $terms;

        $em->persist($trigger);
        $em->flush();

        return $trigger;
    }

    /**
     * @return \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
     */
    public function getIncomingAccountConfig()
    {
        switch ($this->incoming_type) {
            case 'pop3':
                if (!$this->in_pop3_account->user) {
                    return;
                }

                return $this->in_pop3_account;

            case 'imap':
                if (!$this->in_imap_account->user) {
                    return;
                }

                return $this->in_imap_account;

            case 'exchange':
                if (!$this->in_exchange_account->user) {
                    return;
                }

                return $this->in_exchange_account;

            case 'gmail':
                if (!$this->in_gmail_account->user) {
                    return;
                }

                return $this->in_gmail_account;

            case 'office365':
                if (!$this->in_office365_account->user) {
                    return;
                }

                return $this->in_office365_account;

            case 'noop':
                return new NoopConfig();

            default:
                return;
        }
    }

    /**
     * @return \Application\DeskPRO\Email\EmailAccount\AccountConfigInterface
     */
    public function getOutgoingAccountConfig()
    {
        switch ($this->outgoing_type) {
            case 'smtp':
                if (defined('DPC_IS_CLOUD')) {
                    if (IpUtils::guessIsLocalNetworkHost($this->out_smtp_account->host)) {
                        return new PhpMailConfig();
                    }

                    // try to resolve a hostname
                    /* TODO: We should use a dns lib for resolving hostname so we can specify nameserver
                    because this tends to fail
                    $host = @gethostbyaddr($this->out_smtp_account->host);
                    if (!$host || IpUtils::guessIsLocalNetworkHost($host)) {
                        return new PhpMailConfig();
                    }
                    */
                }

                return $this->out_smtp_account;

            case 'gmail':
                return $this->out_gmail_account;

            case 'office365':
                return $this->out_office365_account;

            case 'php_mail':
                return new PhpMailConfig();

            case 'exchange':
                return $this->out_exchange_account;

            default:

                return;
        }
    }
}
