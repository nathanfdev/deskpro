<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Visitor\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class VisitorIdentifierJavascriptListener implements EventSubscriberInterface
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
        ) {
            return;
        }

        $this->injectScript($response);
    }

    private function injectScript(Response $response)
    {
        $content = $response->getContent();
        $pos     = strripos($content, '</body>');

        // if you change this plase see PortalBundle:SavedForm:auto_submit.html.twig
        if (false !== $pos) {
            $script = <<<'JS'
<script>
!function(){function t(t){for(var n=t+"=",e=document.cookie.split(";"),r=0;r<e.length;r++){for(var o=e[r];" "==o.charAt(0);)o=o.substring(1,o.length);if(0==o.indexOf(n))return o.substring(n.length,o.length)}return null}function n(t,n){for(var e="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789".split(""),r=1===n?e:e.slice(0,26),o=1===n?36:26,i="";t-->0;)i+=r[Math.floor(Math.random()*o)];return i}var e="dp__v",r=t(e);r||(r=Math.ceil((new Date).getTime()/1e3/60)+"-"+n(8,1)+"-"+n(8,1)+"-"+n(6,1)+"-"+n(3,2),document.cookie=e+"="+r+"; path=/")}();
</script>
JS;
            $content = substr($content, 0, $pos).$script.substr($content, $pos);
            $response->setContent($content);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse'],
        ];
    }
}

// I just minified this script online and copy+pasted above
/*
(function(){
function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function randChars(len, set) {
    var charsSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'.split('');
    var useChars = set === 1 ? charsSet : charsSet.slice(0, 26);
    var charLen  = set === 1 ? 36 : 26;
    var res = '';
    while (len-- > 0) {
        res += useChars[Math.floor(Math.random()*charLen)];
    }
    return res;
}

var cookieName = 'dp__v';
var vid = readCookie(cookieName);

// Set the token if it hasn't been created yet
if (!vid) {
    vid = Math.ceil((new Date()).getTime() / 1000 / 60) + '-'
        + randChars(8,1) + '-'
        + randChars(8,1) + '-'
        + randChars(6,1) + '-'
        + randChars(3,2)
    ;
    document.cookie = cookieName+"="+vid+"; path=/";
}
})();
*/
