EMAIL BUNDLE
========================================================================================================================

OUTGOING EMAIL
------------------------------------------------------------------------------------------------------------------------

Outgoing mail in DeskPRO is done in two stages:

    a) An email is created through Swiftmailer which is saved to the DB.
    b) Our own DeskPRO-specific processes go through stored emails and send out (or retry) any that are waiting to go.

## Stage A: Swiftmailer ##

The first stage is the "standard" way to send mail with Symfony+Swiftmailer. We have two customisations:

    1) We have a custom transport which saves email to the database (instead of actually doing work to send
       the message).

    2) We have a custom 'Message' class which knows about email templates and stuff like that.

## Stage B: DeskPRO processes to send mail ##

Then we have purely DeskPRO ways of actually sending a message.

The DeskPRO process works on raw message/rfc822 messages. In the most common case (SMTP) this means we can just send
raw messages through. In other cases such as PHP mail or a web service etc., we can decode messages into simple
message objects.