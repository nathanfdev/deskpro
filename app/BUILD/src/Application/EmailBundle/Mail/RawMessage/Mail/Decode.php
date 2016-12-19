<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\EmailBundle\Mail\RawMessage\Mail;

use Zend\Mime\Mime;
use Zend\Stdlib\ErrorHandler;

class Decode extends \Zend\Mime\Decode
{
    /**
     * {@inheritdoc}
     */
    public static function splitMessage($message, &$headers, &$body, $EOL = Mime::LINEEND, $strict = false)
    {
        if ($message instanceof Headers) {
            $message = $message->toString();
        }
        // check for valid header at first line
        $firstline = strtok($message, "\n");
        if (!preg_match('%^[^\s]+[^:]*:%', $firstline)) {
            $headers = [];
            // TODO: we're ignoring \r for now - is this function fast enough and is it safe to assume noone needs \r?
            $body = str_replace(["\r", "\n"], ['', $EOL], $message);

            return;
        }

        // see @ZF2-372, pops the first line off a message if it doesn't contain a header
        if (!$strict) {
            $parts = explode(':', $firstline, 2);
            if (count($parts) != 2) {
                $message = substr($message, strpos($message, $EOL) + 1);
            }
        }

        // find an empty line between headers and body
        // default is set new line
        if (strpos($message, $EOL.$EOL)) {
            list($headers, $body) = explode($EOL.$EOL, $message, 2);
            // next is the standard new line
        } elseif ($EOL != "\r\n" && strpos($message, "\r\n\r\n")) {
            list($headers, $body) = explode("\r\n\r\n", $message, 2);
            // next is the other "standard" new line
        } elseif ($EOL != "\n" && strpos($message, "\n\n")) {
            list($headers, $body) = explode("\n\n", $message, 2);
            // at last resort find anything that looks like a new line
        } else {
            ErrorHandler::start(E_NOTICE | E_WARNING);
            list($headers, $body) = preg_split("%([\r\n]+)\\1%U", $message, 2);
            ErrorHandler::stop();
        }

        $headers = Headers::fromString($headers, $EOL);
    }
}
