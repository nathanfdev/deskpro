<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Ticket;

use Application\DeskPRO\Entity\Ticket;
use DateTime;
use Orb\Util\Strings;

class BounceDetector extends \Application\DeskPRO\EmailGateway\BounceDetector implements PublicTacAware
{
    /**
     * @var string
     */
    protected $ptacCode;

    /**
     * @var Ticket
     */
    protected $guessedTicket;

    /**
     * @return string
     */
    public function getPtacCode()
    {
        if ($this->ptacCode !== null) {
            if ($this->ptacCode === false) {
                return null;
            }

            return $this->ptacCode;
        }

        $m = null;
        if (preg_match('#(?:PTAC|TICKET)\-([A-Z0-9]+)\.#', $this->reader->getRawHeaders(), $m)) {
            $this->ptacCode = $m[1];
            if ($this->logger) {
                $this->logger->logDebug('Found PTAC: '.$this->ptacCode);
            }
        } else {
            $this->ptacCode = false;
            if ($this->logger) {
                $this->logger->logDebug('No PTAC found');
            }
        }

        // There might be emails as attachments that we should check out
        if (!$this->ptacCode) {
            foreach ($this->reader->getAttachments() as $k => $attach) {
                if ($attach->mime_type == 'message/rfc822') {
                    if ($this->logger) {
                        $this->logger->logDebug("Checking attach #$k {$attach->file_name} for PTAC");
                    }

                    $headers = [];

                    $fp = @fopen($attach->tmp_file, 'r');
                    if (!$fp) {
                        continue;
                    }

                    $limit = 200;
                    while ($limit-- > 0 && !feof($fp)) {
                        $l = @fgets($fp, 2000);
                        if (!$l || trim($l) === '') {
                            break; // stop reading after we have all headers
                        }
                        $headers[] = $l;
                    }

                    @fclose($fp);

                    $headers = implode("\n", $headers);

                    if (preg_match('#(?:PTAC|TICKET)\-([A-Z0-9]+)\.#', $headers, $m)) {
                        $this->ptacCode = $m[1];
                        if ($this->logger) {
                            $this->logger->logDebug('Found PTAC: '.$this->ptacCode);
                        }
                        break; // break out of reading attaches
                    } else {
                        $this->ptacCode = false;
                        if ($this->logger) {
                            $this->logger->logDebug('No PTAC found');
                        }
                    }
                }
            }
        }

        return $this->ptacCode;
    }

    /**
     * Try to guess the ticket this bounce belongs to.
     *
     * @return Ticket
     */
    public function getGuessedTicket()
    {
        if ($this->guessedTicket !== null) {
            if ($this->guessedTicket === false) {
                return null;
            }

            return $this->guessedTicket;
        }

        $this->guessedTicket = false;

        if ($ptac = $this->getPtacCode()) {
            $ticket = $this->em->getRepository(Ticket::class)->getByAccessCode($ptac);
            if ($ticket) {
                $this->guessedTicket = $ticket;

                return $this->guessedTicket;
            }
        }

        $guessedEmails = $this->getGuessedEmailAddresses();
        $foundTicketId = null;
        $body          = $this->reader->getBodyText()->getBodyUtf8();

        foreach ($guessedEmails as $email) {
            if ($this->logger) {
                $this->logger->logDebug(sprintf('Finding last subjects by %s', $email));
            }

            // We limit ticket subjects to recent tickets
            $dateLimitObject = new DateTime();
            $dateLimitObject->sub(new \DateInterval('P3D'));
            $dateLimit = $dateLimitObject->format('Y-m-d');

            $ticketSubjects = $this->em->getConnection()->fetchAllKeyValue("
                SELECT tickets.id, tickets.subject
                FROM tickets
                LEFT JOIN people_emails ON (people_emails.person_id = tickets.person_id)
                WHERE tickets.status IN ('awaiting_user', 'awaiting_agent', 'resolved') AND people_emails.email = ?
                AND (tickets.date_last_agent_reply > ? OR tickets.date_last_user_reply > ?)
                ORDER BY tickets.id DESC
                LIMIT 50
            ", [$email, $dateLimit, $dateLimit]);

            if ($this->originalSubject) {
                foreach ($ticketSubjects as $tid => $subj) {
                    if ($this->logger) {
                        $this->logger->logDebug(sprintf("Trying %d '%s' against original '%s'", $tid, $subj, $this->originalSubject));
                    }
                    if ($this->originalSubject === $subj) {
                        $foundTicketId = $tid;
                        break 2;
                    }
                }
            } else {
                // Try matching the tail of this subject after a ':' if its not too short
                $trySubject = $this->reader->getSubject()->getSubjectUtf8();
                if (($pos = strrpos($trySubject, ':')) !== false) {
                    $trySubject = substr($trySubject, $pos);
                    if ($this->logger) {
                        $this->logger->logDebug(sprintf('Tail subject match: %s', $trySubject));
                    }
                    if (strlen($trySubject) < 10) {
                        if ($this->logger) {
                            $this->logger->logDebug('Tail subject match too short');
                        }
                    } else {
                        foreach ($ticketSubjects as $tid => $subj) {
                            if ($this->logger) {
                                $this->logger->logDebug(sprintf("Trying %d '%s' against trail subject '%s'", $tid, $subj, $trySubject));
                            }
                            if (Strings::endsWith($trySubject, $subj)) {
                                $foundTicketId = $tid;
                                break 2;
                            }
                        }
                    }
                }

                // Fall back on trying to find the subject in the body message
                // Which can be common in "Undelivered" type messages
                foreach ($ticketSubjects as $tid => $subj) {
                    if ($this->logger) {
                        $this->logger->logDebug(sprintf("Trying %d '%s' against body", $tid, $subj));
                    }
                    if (strpos($body, $subj) !== false) {
                        $foundTicketId = $tid;
                        break 2;
                    }
                }
            }
        }

        if ($foundTicketId) {
            $this->guessedTicket = $this->em->find('DeskPRO:Ticket', $foundTicketId);

            return $this->guessedTicket;
        }

        return $this->guessedTicket;
    }

    public function isPublicTac()
    {
        return $this->guessedTicket && $this->ptacCode;
    }
}
