<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\Storage;

use Fetch\Server;

class Imap extends Server
{
    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (!isset($options['host']) ||
            !isset($options['port']) ||
            !isset($options['user']) ||
            !isset($options['password'])) {
            throw new \Exception('Insufficient Parameters');
        }

        parent::__construct($options['host'], $options['port']);

        if (isset($options['secure'])) {
            switch (strtoupper($options['secure'])) {
                case 'SSL':
                    $this->setFlag('ssl');
                    break;
                case 'TLS':
                    $this->setFlag('tls');
                    break;
            }
            if ($options['no_validation']) {
                $this->setFlag('novalidate-cert');
            }
        }

        $this->setAuthentication($options['user'], $options['password']);
    }


    /**
     * @return array an array of IDs
     */
    public function getAllUnseenMessageUids()
    {
        $result = imap_search($this->getImapStream(), 'UNSEEN UNDELETED', SE_UID);

        if ($result === false) {
            return array();
        }

        return $result;
    }


    /**
     * Searches the server for matching emails and retrieves only the IDs
     *
     * @return array an array of matching IDs
     */
    public function getAllMessageUids()
    {
        $result = imap_search($this->getImapStream(), 'ALL UNDELETED', SE_UID);

        if ($result === false) {
            return array();
        }

        return $result;
    }


    /**
     * Gets a raw RFC2822 compatible message
     *
     * @param  int    $uid Unique message id
     * @return string Raw message
     */
    public function getRawMessage($uid)
    {
        $raw_body = imap_fetchbody($this->getImapStream(), $uid, '', FT_UID);

        if($raw_body === false){
            throw new \Exception(sprintf('Failed to retrieve raw body for message'));
        }

        return $raw_body;
    }

    /**
     * @param  int      $uid
     * @return null|int
     */
    public function getMessageSize($uid)
    {
        $results = imap_fetch_overview($this->imapStream, $uid, FT_UID);
        if (!$results) {
            return null;
        }

        $message_overview = array_shift($results);

        return isset($message_overview->size) ? $message_overview->size : null;
    }

    /**
     * Creates a mailbox if it doesnt exist
     *
     * @param  string $mailbox
     * @return bool   True if it was created, false otherwise
     */
    public function ensureMailboxExists($mailbox)
    {
        if (!$this->hasMailBox($mailbox)) {
            $this->createMailBox($mailbox);

            return true;
        }

        return false;
    }

    /**
     * @param int    $uid
     * @param string $new_mailbox
     */
    public function moveMessageMailbox($uid, $new_mailbox)
    {
        imap_mail_move($this->imapStream, "$uid", "$new_mailbox", CP_UID);
        //imap_expunge($this->imapStream);
    }

    /**
     * @param int $uid
     */
    public function deleteMessage($uid)
    {
        imap_delete($this->imapStream, $uid, FT_UID);
        //imap_expunge($this->imapStream);
    }

    /**
     * @param  int    $uid
     * @return string
     */
    public function getRawHeaders($uid)
    {
        return imap_fetchheader($this->imapStream, $uid, FT_UID);
    }

    /**
     * @return bool
     */
    public function clearCaches()
    {
        return imap_gc($this->imapStream, IMAP_GC_ELT|IMAP_GC_ENV|IMAP_GC_TEXTS);
    }

    /**
     * Close the connection
     */
    public function close()
    {
        $this->clearCaches();
    }
}
