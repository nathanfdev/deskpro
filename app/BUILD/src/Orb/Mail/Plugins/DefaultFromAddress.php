<?php

/**
 * Orb.
 */

namespace Orb\Mail\Plugins;

use Orb\Log\Logger;

/**
 * If no 'from' is set on a message, this will give it a default.
 */
class DefaultFromAddress implements \Swift_Events_SendListener
{
    /** @var string */
    protected $from;
    /** @var string */
    protected $name = '';
    /** @var \Orb\Log\Logger */
    protected $logger;

    public function __construct($from, $name = '', Logger $logger = null)
    {
        $this->from   = $from;
        $this->name   = $name;
        $this->logger = $logger;

        if ($this->logger) {
            $this->logger->logInfo(sprintf('[DefaultFromAddress] Default from: %s <%s>', $name, $from));
        }
    }

    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
    }

    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

        if ($this->logger) {
            $this->logger->logInfo(sprintf('[DefaultFromAddress] Checking: %s', print_r($message->getFrom(), true)));
        }

        if (!$message->getFrom()) {
            if ($this->logger) {
                $this->logger->logInfo('[DefaultFromAddress] Setting name and email');
            }
            $message->setFrom($this->from, $this->name);
        } else {
            $from = $message->getFrom();

            if (is_array($from)) {
                foreach ($from as &$v) {
                    if (!$v) {
                        if ($this->logger) {
                            $this->logger->logInfo('[DefaultFromAddress] Setting name');
                        }
                        $v = $this->name;
                    }
                    break;
                }

                $message->setFrom($from);
            } elseif (is_string($from)) {
                // From is a string now,
                // It's either a string of email@example.com or Name <email@example.com>
                // So if its just an email, we want to prepend the default name
                if (\Orb\Validator\StringEmail::isValueValid($from)) {
                    if ($this->logger) {
                        $this->logger->logInfo('[DefaultFromAddress] Setting name');
                    }
                    $from = [$from => $this->name];
                }

                $message->setFrom($from);
            }
        }
    }
}
