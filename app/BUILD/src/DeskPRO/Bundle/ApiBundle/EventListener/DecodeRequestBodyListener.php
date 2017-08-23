<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
