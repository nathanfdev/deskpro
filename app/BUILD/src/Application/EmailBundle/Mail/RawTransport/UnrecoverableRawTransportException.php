<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawTransport;

/**
 * Exception used to represent a transport failure that cannot be fixed
 * by a retry (i.e., dont schedule the email source for a retry).
 */
class UnrecoverableRawTransportException extends \RuntimeException
{
}
