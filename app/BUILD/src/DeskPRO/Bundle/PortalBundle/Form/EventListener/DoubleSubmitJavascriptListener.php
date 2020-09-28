<?php

namespace DeskPRO\Bundle\PortalBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
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

        $this->injectScript($response, $request);
    }

    private function injectScript(Response $response, Request $request)
    {
        $content = $response->getContent();
        $pos     = strripos($content, '</body>');

        $securedCookie = $request->isSecure() ? ';secure' : '';

        // if you change this please see PortalBundle:SavedForm:auto_submit.html.twig
        if (false !== $pos) {
            $script = <<< END
<script>
!function(i){i.dp_refresh_csrf_token=function(){var t="_dp_csrf_token",n=function(t){for(var n=t+"=",e=document.cookie.split(";"),r=0;r<e.length;r++){for(var o=e[r];" "==o.charAt(0);)o=o.substring(1,o.length);if(0==o.indexOf(n))return o.substring(n.length,o.length)}return null}(t),e=/.*\[_dp_csrf_token\]*./;n||(n=(Math.random()+1).toString(36).substring(2,17)+(Math.random()+1).toString(36).substring(2,17),document.cookie=t+"="+n+"; path=/; SameSite=None; Secure");for(var r=document.getElementsByTagName("input"),o=1;o<r.length;o++)"hidden"==r[o].getAttribute("type")&&r[o].getAttribute("name").match(e)&&(r[o].value=n);i.dp_get_csrf_token=function(){return n}},i.dp_refresh_csrf_token()}(window);
</script>
END;
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
(function(win) {
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

win.dp_refresh_csrf_token = function() {
    var cookieName = '_dp_csrf_token';
    var token = readCookie(cookieName);
    var fieldNamePattern = /.*\[_dp_csrf_token\]*./;

    // Set the token if it hasn't been created yet
    // This is a session cookie, so it'll be re-created every time
    // the user comes back.
    if (!token) {
        token = (Math.random()+1).toString(36).substring(2, 17)+(Math.random()+1).toString(36).substring(2, 17);
        document.cookie = cookieName+"="+token+"; path=/; SameSite=None; Secure";
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
}

win.dp_refresh_csrf_token();

})(window);
*/
