<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Storage;

use Fetch\Server;

class Imap extends Server
{
    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $options['password'] = (string) @$options['password'];
        $options['port']     = $options['port'] ?: 143;

        if (!isset($options['host']) ||
            !isset($options['user'])) {
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
            return [];
        }

        return $result;
    }

    /**
     * Searches the server for matching emails and retrieves only the IDs.
     *
     * @return array an array of matching IDs
     */
    public function getAllMessageUids()
    {
        $result = imap_search($this->getImapStream(), 'ALL UNDELETED', SE_UID);

        if ($result === false) {
            return [];
        }

        return $result;
    }

    /**
     * Gets a raw RFC2822 compatible message.
     *
     * @param int $uid Unique message id
     *
     * @return string Raw message
     */
    public function getRawMessage($uid)
    {
        $raw_body = imap_fetchbody($this->getImapStream(), $uid, '', FT_UID);

        if ($raw_body === false) {
            throw new \Exception(sprintf('Failed to retrieve raw body for message'));
        }

        return $raw_body;
    }

    /**
     * @param int $uid
     *
     * @return null|int
     */
    public function getMessageSize($uid)
    {
        $results = imap_fetch_overview($this->imapStream, $uid, FT_UID);
        if (!$results) {
            return;
        }

        $message_overview = array_shift($results);

        return isset($message_overview->size) ? $message_overview->size : null;
    }

    /**
     * Creates a mailbox if it doesnt exist.
     *
     * @param string $mailbox
     *
     * @return bool True if it was created, false otherwise
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
     * @param int $uid
     *
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
        return imap_gc($this->imapStream, IMAP_GC_ELT | IMAP_GC_ENV | IMAP_GC_TEXTS);
    }

    /**
     * Close the connection.
     */
    public function close()
    {
        $this->clearCaches();
    }
}
