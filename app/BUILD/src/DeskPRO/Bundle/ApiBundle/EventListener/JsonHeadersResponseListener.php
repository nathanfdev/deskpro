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

use Application\DeskPRO\NewSettings\SettingsResolver;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class JsonHeadersResponseListener.
 */
class JsonHeadersResponseListener implements EventSubscriberInterface
{
    const INCLUDE_HEADERS_PARAM = 'include_headers';

    public static $excluded_headers = [
        'set-cookie',
    ];

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SettingsResolver
     */
    protected $resolver;

    /**
     * Constructor.
     *
     * @param SerializerInterface $serializer
     * @param SettingsResolver    $resolver
     */
    public function __construct(SerializerInterface $serializer, SettingsResolver $resolver)
    {
        $this->serializer = $serializer;
        $this->resolver   = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', 1025],
        ];
    }

    /**
     * @internal
     *
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();
        $location = $this->stripLocationHeader($request, $response);

        if ($request->query->has(self::INCLUDE_HEADERS_PARAM) || $location) {
            // add the "headers" node to the json response body
            $body = $response->getContent();
            if ($body) {
                $data = json_decode($body);
            } else {
                $data = new \stdClass();
            }


            if (is_array($data)) {
                $data['headers'] = $this->extractHeaderArray($response, $location);
            } else {
                $data->headers = $this->extractHeaderArray($response, $location);
            }

            $newBody = json_encode($data);

            $response->setContent($newBody);
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
     * @param string   $location
     *
     * @return array
     */
    private function extractHeaderArray(Response $response, $location)
    {
        $headers   = (string) $response->headers;
        $headerBag = explode("\r\n", $headers);

        $jsonHeaders = [
            'status-code' => $response->getStatusCode(),
        ];
        foreach ($headerBag as $key => $val) {
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

            $jsonHeaders[$header_name] = $header_val;
        }

        if ($location) {
            $jsonHeaders['location'] = $location;
        }

        return $jsonHeaders;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return string
     */
    protected function stripLocationHeader(Request $request, Response $response)
    {
        // there is no location header
        if (!$location = $response->headers->get('Location')) {
            return;
        }

        // or no response body
        if (!$response->getContent()) {
            return;
        }

        // skip non-IIS servers
        if (false === strpos(strtolower($request->server->get('SERVER_SOFTWARE')), 'microsoft-iis')) {
            return;
        }

        // disabled by cfg
        if ($this->resolver->getGlobalSettings()->get('api.disable_location_header_strip')) {
            return;
        }

        $response->headers->remove('Location');

        return $location;
    }
}
