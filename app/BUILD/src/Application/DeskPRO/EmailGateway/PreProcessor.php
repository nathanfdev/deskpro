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
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\Item\AuthenticationResults;
use Application\DeskPRO\Entity\EmailSource;
use Application\DeskPRO\Entity\TicketMessage;

class PreProcessor extends AbstractGatewayProcessor
{
    /** @var string|null */
    protected $error = null;
    /** @var string */
    protected $error_type = 'rejected';
    /** @var array|null */
    protected $source_info = null;

    public function run()
    {
        // TODO [cloudspam] proper cloud spam checker/handling
        if (defined('DPC_IS_CLOUD') && \DpSys\License::getLicense()->isDemo() && !DPC_SITE_IS_APPROVED) {
            $em = $this->getEm();
            $db = $this->getDb();

            $emailCount = $db->fetchColumn('
                SELECT COUNT(*)
                FROM email_sources
                WHERE date_created > ?
            ', [date('Y-m-d H:i:s', time() - 3600)]);

            if ($emailCount && $emailCount >= 150) {
                \DpShutdown::add(function () use ($em) {
                    $tmpdata = new \Application\DeskPRO\Entity\TmpData();
                    $tmpdata->setType('cancel_for_abuse');
                    $tmpdata->setData('message', '>150 incoming emails in under an hour');
                    $tmpdata->date_expire = new \DateTime('+30 minutes');
                    $em->persist($tmpdata);
                    $em->flush();

                    $url = DP_MA_SERVER_SECURE.'/cloud/call/'.DPC_SITE_ID.'/'.$tmpdata->getCode();

                    try {
                        $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
                        $client->setMethod(\Zend\Http\Request::METHOD_GET);
                        $client->setUri($url);
                        $r = $client->send();
                    } catch (\Exception $e) {
                        error_log('Failed to cancel site: '.$e->getMessage());
                    }
                });

                $this->error = EmailSource::ERR_RATE_LIMIT;

                return;
            }
        }

        //------------------------------
        // Empty From
        //------------------------------

        $from = $this->reader->getFromAddress()->getEmail();
        if (!$from) {
            $this->error = EmailSource::ERR_FROM_MISSING;

            return;
        }

        //------------------------------
        // Dupe Detection
        //------------------------------

        // TODO: Put this behind an option
        // It's possible IDs are non-unique (e.g. by some automated systems)
        // Until then, this is disabled.
        /*
        if ($id = $this->reader->getId()) {
            if ($old = $this->getEm()->getRepository('DeskPRO:TicketMessage')->getDupeByMessageID($id)) {
                $this->error = EmailSource::ERR_DUPE;
                $this->source_info[] = 'Ticket ID: '. $old->message->ticket['id'];
                $this->source_info[] = 'Email Message ID: '. $id;
                return;
            }
        }
        */

        //------------------------------
        // Invalid From
        //------------------------------

        $validator = new \Orb\Validator\StringEmail(['reject_example' => true]);

        if (!$validator->isValid($from)) {
            $this->error         = EmailSource::ERR_FROM_INVALID;
            $this->source_info   = [];
            $this->source_info[] = 'Read from address: '.$from;
            $this->source_info[] = "Errors:\n\n".$validator->getErrorsDebug();

            return;
        }

        //------------------------------
        // From is a know gateway address
        //------------------------------

        $accountManager = App::$container->getEmailAccountManager();
        if ($foundAccount = $accountManager->findAccountForEmailAddress($from)) {
            $this->error         = EmailSource::ERR_FROM_GATEWAY;
            $this->source_info[] = 'Read from address: '.$from;
            $this->source_info[] = 'Matched account: '.$foundAccount->id;
            $this->source_info[] = 'Account addresses: '.implode(', ', $foundAccount->getAllAddresses());

            return;
        }

        //------------------------------
        // From is a banned address
        //------------------------------

        $match = null;
        if (App::getOrm()->getRepository('DeskPRO:BanEmail')->isEmailBanned($from, $match)) {
            $this->error         = EmailSource::ERR_FROM_BANNED;
            $this->source_info[] = 'Read from address: '.$from;
            $this->source_info[] = 'Matched banned email: '.$match;

            return;
        }

        //------------------------------
        // Check for empty message
        //------------------------------

        $subj     = trim($this->reader->getSubject()->getSubject());
        $message  = trim($this->reader->getBodyHtml()->getBody());
        $message2 = trim($this->reader->getBodyText()->getBody());
        $attach   = $this->reader->getAttachments();

        if (!$subj && !$message && !$message2 && !$attach) {
            $this->error = EmailSource::ERR_EMPTY;

            return;
        }

        //------------------------------
        // Check if date is older than start_date_limit
        // on the account
        //------------------------------

        if ($this->account->date_read_start && $emailDate = $this->reader->getDate() && App::getSetting('core_email.enable_date_limit_rejection')) {
            if ($emailDate < $this->account->date_read_start) {
                $this->error         = EmailSource::ERR_DATE_LIMIT;
                $this->source_info[] = 'Gateway date limit: '.$this->account->date_read_start->format(\DateTime::RFC2822);
                $this->source_info[] = 'Message date: '.$emailDate->format(\DateTime::RFC2822);

                return;
            }
        }

        //--------------------------------
        // Validate email SPF and DKIM header
        //--------------------------------

        if (App::getSetting('core_tickets.reject_spf_level') || App::getSetting('core_tickets.reject_dkim_level')) {
            $authenticationResults = $this->reader->getAuthenticationResults();

            $spfLevel  = App::getSetting('core_tickets.reject_spf_level');
            $dkimLevel = App::getSetting('core_tickets.reject_dkim_level');

            /** @var AuthenticationResults $authenticationResult */
            foreach ($authenticationResults as $authenticationResult) {
                if ($spfLevel && in_array($authenticationResult->getSpfResult(), explode(',', $spfLevel))) {
                    $this->error         = EmailSource::ERR_SPF_REJECT;
                    $this->source_info[] = 'SPF servId: '.$authenticationResult->getAuthservId();
                    $this->source_info[] = 'SPF result: '.$authenticationResult->getSpfResult();

                    return;
                }
                if ($dkimLevel && in_array($authenticationResult->getDkimResult(), explode(',', $dkimLevel))) {
                    $this->error         = EmailSource::ERR_DKIM_REJECT;
                    $this->source_info[] = 'DKIM servId: '.$authenticationResult->getAuthservId();
                    $this->source_info[] = 'DKIM result: '.$authenticationResult->getDkimResult();

                    return;
                }
            }
        }

        unset($subj, $message, $message2, $attach);
    }

    public function isValid()
    {
        return $this->error === null;
    }

    /**
     * 'error' or 'rejected'.
     *
     * @return string
     */
    public function getErrorType()
    {
        return $this->error_type;
    }

    public function getErrorCode()
    {
        return $this->error;
    }

    public function getSourceInfo()
    {
        if (!$this->source_info) {
            return;
        }

        if (!is_array($this->source_info)) {
            $this->source_info = [$this->source_info];
        }

        return $this->source_info;
    }
}
