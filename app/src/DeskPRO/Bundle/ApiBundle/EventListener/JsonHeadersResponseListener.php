<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\EventListener;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonHeadersResponseListener implements EventSubscriberInterface
{
    const INCLUDE_HEADERS_PARAM = 'include_headers';

    public static $excluded_headers = array(
        'set-cookie',
    );

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onResponse', -1),
        );
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        if ($request->query->has(self::INCLUDE_HEADERS_PARAM)) {

            // add the "headers" node to the json response body

            $body = $response->getContent();
            $json = $this->serializer->deserialize($body, 'array', 'json');

            $json['headers'] = $this->extractHeaderArray($response);

            $new_body = $this->serializer->serialize($json, 'json');

            $response->setContent($new_body);
        }
    }

    /**
     * @param $header_split
     *
     * @return array
     */
    private function getHeaderStringVal($header_split)
    {
        $header_values      = array_splice($header_split, 1, count($header_split) - 1);
        $header_value_split = implode(':', $header_values);

        return trim($header_value_split);
    }

    /**
     * @param Response $response
     *
     * @return array
     */
    private function extractHeaderArray(Response $response)
    {
        $headers = (string) $response->headers;

        $header_bag = explode("\r\n", $headers);

        $json_headers = array(
            'status-code' => $response->getStatusCode(),
        );
        foreach ($header_bag as $key => $val) {
            $header_split = explode(':', $val);

            // the first string behind the first ":"
            $header_name = strtolower(trim($header_split[0]));

            // needs to glue together the various header values
            $header_val = $this->getHeaderStringVal($header_split);

            if (in_array($header_name, self::$excluded_headers)) {
                // a header we exclude
                continue;
            }

            if (strlen($header_name) === 0 || strlen($header_val) === 0) {
                // we need both a name and a val
                continue;
            }

            $json_headers[$header_name] = $header_val;
        }

        return $json_headers;
    }
}
