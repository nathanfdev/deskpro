<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\AntiAbuse;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use Doctrine\ORM\EntityManager;
use DpTest\PortalTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * The anti-abuse system will throw an exception to give the client a different response sometimes, so we test
 * that functionality here.
 *
 * For specific tests around the various lockout/rate-limit logic please see the EventListener tests.
 */
class AntiAbuseTest extends PortalTestCase
{
    public function testCheckFiresTheEventPassed()
    {
        $event = new LoginAbuseCheck(new Person());

        $em               = $this->prophesize(EntityManager::class);
        $event_dispatcher = $this->prophesize(EventDispatcher::class);

        $anti_abuse = new AntiAbuse($event_dispatcher->reveal(), $em->reveal());

        $anti_abuse->check($event);

        $event_dispatcher->dispatch(AntiAbuse::getEventName(AntiAbuse::ACTION_LOGIN), $event)->shouldHaveBeenCalled();
    }

    /**
     * @expectedException \DeskPRO\Bundle\AppBundle\AntiAbuse\Exception\AntiAbuseException
     */
    public function testAntiAbuseExceptionThrownWhenResponseRequired()
    {
        // the anti abuse system will throw an exception when a response is required
        // you dont have to catch this in your service/controller because
        // an EXCEPTION listener will catch it and return the correct
        // response

        $event = new LoginAbuseCheck(new Person());

        // an event listener would actually set this when necessary
        // you dont set this (setResponse) in client code
        $event->setResponse(new RedirectResponse('http://google.com'));
        $event->markResponseRequired();

        $em               = $this->prophesize(EntityManager::class);
        $event_dispatcher = $this->prophesize(EventDispatcher::class);

        $anti_abuse = new AntiAbuse($event_dispatcher->reveal(), $em->reveal());

        $anti_abuse->check($event);
    }
}
