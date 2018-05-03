<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks;

use Symfony\Component\HttpFoundation\Request;

class Converters
{
    /**
     * @param Request $from
     * @return null|string
     */
    public static function toContentString(Request $from)
    {
        $content = null;
        $contentType = $from->headers->get('CONTENT_TYPE');

        if ($from->isMethod('POST')) {
            if (0 === strpos($contentType, 'application/x-www-form-urlencoded') || 0 === strpos($contentType, 'multipart/form-data')) {
                $params = $from->request->all();
                return http_build_query($params, '', '&', PHP_QUERY_RFC1738);
            }

            return (string) $from->getContent();
        }

        return null;
    }

    /**
     * @param Request $from
     *
     * @return WebhookRequest
     */
    public static function toWebhookRequest(Request $from)
    {
        $query   = $from->query->all();
        $queryString = $from->getQueryString();
        $content = Converters::toContentString($from);
        $headers = $from->headers->all();

        return new WebhookHttpRequest($queryString, $query, $content, $headers);
    }
}
