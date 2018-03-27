<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DoubleSubmitJavascriptListener implements EventSubscriberInterface
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
!function(t){function n(t){for(var n=t+"=",e=document.cookie.split(";"),r=0;r<e.length;r++){for(var o=e[r];" "==o.charAt(0);)o=o.substring(1,o.length);if(0==o.indexOf(n))return o.substring(n.length,o.length)}return null}var e="_dp_csrf_token",r=n(e),o=/.*\[_dp_csrf_token\]*./;r||(r=(Math.random()+1).toString(36).substring(2,17)+(Math.random()+1).toString(36).substring(2,17),document.cookie=e+"="+r+"; path=/");for(var i=document.getElementsByTagName("input"),u=1;u<i.length;u++)"hidden"==i[u].getAttribute("type")&&i[u].getAttribute("name").match(o)&&(i[u].value=r);t.dp_get_csrf_token=function(){return r}}(window);
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
(function(win){
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

var cookieName = '_dp_csrf_token';
var token = readCookie(cookieName);
var fieldNamePattern = /.*\[_dp_csrf_token\]*./;

// Set the token if it hasn't been created yet
// This is a session cookie, so it'll be re-created every time
// the user comes back.
if (!token) {
    token = (Math.random()+1).toString(36).substring(2, 17)+(Math.random()+1).toString(36).substring(2, 17);
    document.cookie = cookieName+"="+token+"; path=/";
}

var inputs = document.getElementsByTagName('input');
for (var i = 1; i < inputs.length; i++) {
    if (inputs[i].getAttribute('type') == 'hidden') {
        if (inputs[i].getAttribute('name').match(fieldNamePattern)) {
            inputs[i].value=token;
        }
    }
}

win.dp_get_csrf_token = function() {
    return token;
};
})(window);
*/
