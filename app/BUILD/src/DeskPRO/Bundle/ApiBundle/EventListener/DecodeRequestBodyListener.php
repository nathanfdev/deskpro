<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use FOS\RestBundle\Decoder\DecoderProviderInterface;
use FOS\RestBundle\EventListener\BodyListener;
use FOS\RestBundle\Normalizer\ArrayNormalizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class DecodeRequestBodyListener extends BodyListener
{
    private $nonDecodableContentTypeNames = [
        'zip'
    ];

    /**
     * Constructor.
     *
     * @param DecoderProviderInterface $decoderProvider
     * @param bool                     $throwExceptionOnUnsupportedContentType
     * @param ArrayNormalizerInterface $arrayNormalizer
     * @param bool                     $normalizeForms
     */
    public function __construct(
        DecoderProviderInterface $decoderProvider,
        $throwExceptionOnUnsupportedContentType = false,
        ArrayNormalizerInterface $arrayNormalizer = null,
        $normalizeForms = false
    ) {

        parent::__construct($decoderProvider, $throwExceptionOnUnsupportedContentType, $arrayNormalizer, $normalizeForms);
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->canDecodeRequestBody($request)) {
            parent::onKernelRequest($event);
        }
    }

    private function canDecodeRequestBody(Request $request)
    {
        $contentType = $request->headers->get('Content-Type');
        if (empty($contentType)) {
            return true;
        }

        $requestFormatName = $request->getFormat($contentType);
        return !in_array($requestFormatName, $this->nonDecodableContentTypeNames);
    }
}
