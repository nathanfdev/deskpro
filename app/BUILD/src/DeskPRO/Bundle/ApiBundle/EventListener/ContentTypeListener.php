<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ContentTypeListener.
 */
class ContentTypeListener implements EventSubscriberInterface
{
    /** @var array */
    private $ignoredContentTypes = [];

    /**
     * Constructor.
     *
     * @param array $ignoredContentTypes
     */
    public function __construct(array $ignoredContentTypes)
    {
        $this->ignoredContentTypes = $ignoredContentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 200],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $contentType = $request->headers->get('Content-Type');
        if (!empty($contentType)) {
            $requestFormatName = $request->getFormat($contentType);
            if (!empty($requestFormatName) && in_array($requestFormatName, $this->ignoredContentTypes)) {
                return;
            }
        }

        $request = $event->getRequest();
        $content = $request->getContent();

        if (preg_match('#^/api/v2/twilio_callbacks/#', $request->getPathInfo())) {
            return;
        }
        if (preg_match('#^/api/v2/blobs#', $request->getPathInfo())) {
            return;
        }
        if (preg_match('#^/api/v2/api_tokens/user_sources/\d+/callback#', $request->getPathInfo())) {
            return;
        }

        // 'Content-Type' header could be `text/plain` so force `application/json` format
        // if we get valid json body content
        // otherwise we will get unsupported format exception
        // because fos rest bundle will try to decode the request body from `text/plain`
        if ($contentType === 'text/plain' || ($content && is_string($content) && @json_decode($content))) {
            $request->setFormat('json', 'application/json');
            $request->attributes->set('_format', 'json');
            $request->attributes->set('media_type', 'json');
            $request->headers->set('Content-Type', 'application/json');
        }
    }
}
