<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Helper;

use Symfony\Component\HttpFoundation\Request;

class LowTemplateHelper
{
    /**
     * @param Request $request
     * @param Request $request
     * @param string  $html
     *
     * @return string
     */
    public static function injectAdminRedirect(Request $request, $html)
    {
        $url          = addslashes($request->getUriForPath('/admin/admin-interface')).'#';
        $redirectHtml = <<<HTML
<script>
(function() {
  var hash = window.location.hash.substr(1);
  var m    = hash.match(/^admin:(.*?)$/);
  var path = window.location.pathname + '';
  
  if (m && !path.match(/admin\-interface\/?$/)) {
    window.location = '{$url}' + m[1];
  }
})();
</script>
HTML;

        $html = str_replace('</head>', "{$redirectHtml}</head>", $html);

        return $html;
    }
}
