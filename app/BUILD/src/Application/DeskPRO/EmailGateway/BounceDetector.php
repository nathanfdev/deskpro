<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\Config\UserFileConfig;
use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Doctrine\ORM\EntityManager;
use Orb\Log\Logger;
use Orb\Util\Strings;

class BounceDetector
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
     */
    protected $reader;

    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    /**
     * @var string[]
     */
    protected $patterns;

    /**
     * @var string
     */
    protected $originalSubject;

    /**
     * @var string[]
     */
    protected $guessedEmailAddresses;

    public function __construct(AbstractReader $reader, EntityManager $em)
    {
        $this->reader = $reader;
        $this->em     = $em;
    }

    /**
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return string[]
     */
    public function getPatterns()
    {
        if ($this->patterns !== null) {
            return $this->patterns;
        }

        $patternConfig  = new UserFileConfig('bounce-subject-patterns');
        $this->patterns = $patternConfig->all();

        return $this->patterns;
    }

    /**
     * @return bool
     */
    public function isBounced()
    {
        // Subject check first, which also populates original_subject
        // which is used again when detecting the ticket this belongs to

        $subject = $this->reader->getSubject()->getSubjectUtf8();

        foreach ($this->getPatterns() as $pattern) {
            $m = null;
            if (preg_match($pattern, $subject, $m)) {
                if (isset($m['subject'])) {
                    $this->originalSubject = $m['subject'];
                }
                if ($this->logger) {
                    $this->logger->logDebug('Is bounced based on subject match: '.$pattern);
                }

                return true;
            }
        }

        $failed = $this->reader->getHeader('X-Failed-Recipients');
        if ($failed && $failed->getHeader()) {
            if ($this->logger) {
                $this->logger->logDebug('Is bounced based on X-Failed-Recipients');
            }

            return true;
        }

        $failed = strpos($this->reader->getHeader('Content-Type')->getHeader(), 'multipart/report') !== false;
        if ($failed) {
            if ($this->logger) {
                $this->logger->logDebug('Is bounced based on Content-Type (multipart/report) header');
            }

            return true;
        }

        // A custom header, can be used to explicitly mark message as a bounce (e.g., for debug or custom rules in mail server)
        $failed = $this->reader->getHeader('X-Is-Bounce');
        if ($failed && $failed->getHeader()) {
            if ($this->logger) {
                $this->logger->logDebug('Is bounced based on X-Is-Bounce');
            }

            return true;
        }

        $from             = $this->reader->getFromAddress();
        $postmasterConfig = new UserFileConfig('postmaster-emails');
        foreach ($postmasterConfig as $pattern) {
            if (preg_match($pattern, $from->email)) {
                if ($this->logger) {
                    $this->logger->logDebug('Is bounced based on postmaster pattern #$k $pattern matching from address '.$from->email);
                }

                return true;
            }
        }

        if ($this->logger) {
            $this->logger->logDebug('Not a bounce');
        }

        return false;
    }

    /**
     * Try to find possible addresses to match on.
     *
     * @return string[]
     */
    public function getGuessedEmailAddresses()
    {
        if ($this->guessedEmailAddresses !== null) {
            return $this->guessedEmailAddresses;
        }

        $this->guessedEmailAddresses = [];

        // The actual From address should be tried too
        $this->guessedEmailAddresses[] = $this->reader->getFromAddress()->getEmail();

        if ($failed = $this->reader->getHeader('X-Failed-Recipients')) {
            foreach ($failed->getAllParts() as $email) {
                $this->guessedEmailAddresses[] = strtolower($email);
                if ($this->logger) {
                    $this->logger->logDebug('Found email via X-Failed-Recipients: '.$email);
                }
            }
        }

        // Find original message part
        $m = null;
        if (preg_match_all('#^To: (.*?)$#imu', $this->reader->getBodyText()->getBodyUtf8(), $m, \PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $email = Strings::extractRegexMatch('#<((.*?)@(.*?))>#', $match[1]);
                if (!$email) {
                    $email = Strings::extractRegexMatch('#((.*?)@(.*?))#', $match[1]);
                }

                if ($email) {
                    $this->guessedEmailAddresses[] = strtolower($email);
                    if ($this->logger) {
                        $this->logger->logDebug('Found email via body: '.$email);
                    }
                }
            }
        }

        $this->guessedEmailAddresses = array_unique($this->guessedEmailAddresses);

        return $this->guessedEmailAddresses;
    }
}
