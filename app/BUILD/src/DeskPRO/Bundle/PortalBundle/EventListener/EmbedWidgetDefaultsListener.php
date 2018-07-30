<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class EmbedWidgetDefaultsListener.
 */
class EmbedWidgetDefaultsListener implements EventSubscriberInterface
{
    /**
     * @var PortalModeStorage
     */
    private $modeStorage;

    /**
     * Constructor.
     *
     * @param PortalModeStorage $modeStorage
     */
    public function __construct(PortalModeStorage $modeStorage)
    {
        $this->modeStorage = $modeStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest'],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        $mode = $this->modeStorage->getMode();
        if (!$mode || !$mode->isFrameEmbed()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request) {
            return;
        }
        if (!$event->isMasterRequest()) {
            return;
        }

        if ($request->query->has('ticket_form_defaults') && $request->hasSession()) {
            $request->getSession()->set('ticket_form_defaults', $request->query->get('ticket_form_defaults'));
        }
    }
}
