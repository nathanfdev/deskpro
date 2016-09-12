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

namespace Application\EmailBundle\Event\Subscriber;

use Application\EmailBundle\EntityRepository\SendmailSourceStatusRepository;
use Application\EmailBundle\Event\Mail;
use Application\EmailBundle\Event\SendGrid as SendGridEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class Sendgrid implements EventSubscriberInterface
{
    /**
     * @var SendmailSourceStatusRepository
     */
    protected $rep;

    public function __construct(SendmailSourceStatusRepository $rep)
    {
        $this->rep = $rep;
    }

    public static function getSubscribedEvents()
    {
        return [
            Mail::BOUNCE           => 'addStatusRecord',
            Mail::CLICK            => 'addStatusRecord',
            Mail::DEFERRED         => 'addStatusRecord',
            Mail::DELIVERED        => 'addStatusRecord',
            Mail::DROPPED          => 'addStatusRecord',
            Mail::OPEN             => 'addStatusRecord',
            Mail::PROCESSED        => 'addStatusRecord',
            Mail::SPAMREPORT       => 'addStatusRecord',
            KernelEvents::RESPONSE => 'flush',
        ];
    }

    /**
     * @param SendGridEvent $event
     * @param $eventName
     * @param EventDispatcher $dispatcher
     */
    public function addStatusRecord(SendGridEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        if (!$ref = $event->get('smtp-id')) {
            throw new \InvalidArgumentException();
        }

        if (!$email = $event->get('email')) {
            throw new \InvalidArgumentException();
        }

        $refparts = explode('@', trim($ref, '<>'));
        if (!$ref = @$refparts[0]) {
            throw new \InvalidArgumentException();
        }

        $reason = $event->get('reason') ?: 'ok';
        $data   = $event->all();
        unset($data['smtp-id'], $data['email'], $data['reason'], $data['event']);

        $this->rep->enqueueStatus(
            $ref,
            $email,
            $eventName,
            $reason,
            json_encode($data)
        );
    }

    /**
     * @throws \Exception
     */
    public function flush()
    {
        $this->rep->flush();
    }
}
