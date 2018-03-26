<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Controller;

use Application\DeskPRO\JIRA\WebhookHandler;
use Application\DeskPRO\Service\JIRA;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JIRAWebhookController extends Controller
{
    /**
     * JIRA webhook endpoint.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handleAction(Request $request)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');

        $content = $request->getContent();

        /** @var JIRA $js */
        $js = $this->get(JIRA::NAME);
        if (!$js->isEnabled()) {
            $response->setContent('JIRA app is not enabled');

            return $response;
        }

        if (!$json = json_decode($content, true)) {
            $response->setContent('Failed to decode JSON payload');

            return $response;
        }

        $handler = new WebhookHandler($this->container);
        if ($handler->handle($json)) {
            $response->setContent('OK');
        } else {
            $response->setContent('FAIL');
        }

        return $response;
    }
}
