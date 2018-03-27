<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\HitTrack\EventListener;

use DeskPRO\Bundle\AppBundle\Entity\HitRecord;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HitTrackJavascriptListener implements EventSubscriberInterface
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request  = $event->getRequest();

        if (!$event->isMasterRequest()) {
            return;
        }

        // do not modify XML HTTP Requests
        if ($request->isXmlHttpRequest()) {
            return;
        }

        if ($response->isRedirection()
            || ($response->headers->has('Content-Type') && false === strpos($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $request->getRequestFormat()
            || !$response->isSuccessful()
        ) {
            return;
        }

        $this->injectScript($response, $this->getPageInfo($request));
    }

    private function injectScript(Response $response, array $pageinfo = null)
    {
        $content = $response->getContent();
        $pos     = strripos($content, '</body>');

        // if you change this plase see PortalBundle:SavedForm:auto_submit.html.twig
        if (false !== $pos) {
            if ($pageinfo) {
                $pre_script = <<<JS
<script type="text/javascript">
var DP_PAGE_TYPE = '{$pageinfo['type']}';
var DP_PAGE_ID   = '{$pageinfo['id']}';
</script>
JS;
            } else {
                $pre_script = '';
            }

            if (!file_exists(DP_WEB_ROOT.'/pub/build/hit_recorder.min.js')) {
                $script = '';
            } else {
                $script = file_get_contents(DP_WEB_ROOT.'/pub/build/hit_recorder.min.js');
            }

            // the replace() call is to trim trailing slashes
            $script = str_replace('__DP_URL__', 'window.DESKPRO_ROOT_URL.replace(/\/+$/, \'\')', $script);
            $script = "$pre_script<script type=\"text/javascript\">{$script}</script>";

            $content = substr($content, 0, $pos).$script.substr($content, $pos);
            $response->setContent($content);
        }
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getPageInfo(Request $request)
    {
        static $route_to_info = [
            'portal_news_view' => [
                'type'   => HitRecord::PAGETYPE_NEWS,
                'object' => 'post',
            ],
            'portal_kb_view' => [
                'type'   => HitRecord::PAGETYPE_ARTICLE,
                'object' => 'article',
            ],
            'portal_feedback_view' => [
                'type'   => HitRecord::PAGETYPE_FEEDBACK,
                'object' => 'item',
            ],
            'portal_downloads_view' => [
                'type'   => HitRecord::PAGETYPE_DOWNLOAD,
                'object' => 'file',
            ],
        ];

        $route = $request->attributes->get('_route');
        if ($route && isset($route_to_info[$route])) {
            $obj_key = $route_to_info[$route]['object'];
            if ($request->attributes->has($obj_key)) {
                $obj = $request->attributes->get($obj_key);

                return [
                    'type' => $route_to_info[$route]['type'],
                    'id'   => $obj->getId(),
                ];
            }
        }

        return ['type' => 'deskpro', 'id' => 'page'];
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse'],
        ];
    }
}
