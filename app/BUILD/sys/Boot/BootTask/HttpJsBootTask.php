<?php

namespace DpSys\Boot\BootTask;

use GuzzleHttp\Psr7;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This has two purposes:.
 *
 * a) Intercepts legacy JS requests for old-style side butotn widget and nulls
 * them so they don't do anything.
 *
 * b) Converts old-style chat widget into new chat widget
 *
 * c) And finally, routes hotlinked widget code to the proper asset version for
 * based on $env.
 */
class HttpJsBootTask implements BootTaskInterface
{
    /**
     * @var \DpRun\DpEnv
     */
    private $env;

    /**
     * @var Request
     */
    private $request;

    public function run(\DpRun\DpEnv $env, array $resources)
    {
        $this->env = $env;

        /** @var Request $request */
        $request       = $resources['request'];
        $this->request = $request;

        $path = $request->getPathInfo();

        // Some very old embeds might have this in an iframe
        if (preg_match('#^/tickets/new-simple(?:/(?P<depId>\d+))?#', $path, $m)) {
            if (!empty($m['depId'])) {
                $res = new RedirectResponse($request->getUriForPath('/focus-win/new-ticket/'.$m['depId']), 301);
            } else {
                $res = new RedirectResponse($request->getUriForPath('/focus-win/new-ticket/'.$m['depId']), 301);
            }

            $res->sendHeaders();
            $res->sendContent();
            exit;
        }

        if ($legacyWidget = $this->getLegacyAsset($path)) {
            $this->serveLegacyWidget($legacyWidget);
        } elseif ($dynAsset = $this->getDynAsset($path)) {
            $this->serveDynAsset($dynAsset);
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getLegacyAsset($path)
    {
        if (substr($path, 0, '4') === '/web' && (substr($path, 0, '5') === '/web/') || preg_match('#^/web\d+/#', $path)) {
            $m = null;
            if (preg_match('#^/web\d*/javascripts/DeskPRO/User/(?P<widgetType>ChatWidget/ChatWidget\.js|WebsiteWidget/Overlay\.js|TicketFormWidget/TicketFormWidget\.js|HelpdeskWidget/HelpdeskWidget\.js)#', $path, $m)) {
                return $m['widgetType'];
            }
        }

        return;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getDynAsset($path)
    {
        if ($path === '/dyn-assets/inst_info.js') {
            return 'inst_info.js';
        }

        // everything is going through dyn-assets, when it's set in DESKPRO_ASSETS_URL
        if (substr($path, 0, 12) === '/dyn-assets/') {
            return preg_replace('#^/dyn\-assets/#', '', $path);
        }

        return;
    }

    //###################################################################################################################
    // Legacy widgets
    //###################################################################################################################

    private function serveLegacyWidget($widget)
    {
        switch ($widget) {
            case 'ChatWidget/ChatWidget.js':
                $code = $this->getChatWidget();
                break;
            case 'TicketFormWidget/TicketFormWidget.js':
                $code = $this->getFormWidget();
                break;
            case 'WebsiteWidget/Overlay.js':
            case 'HelpdeskWidget/HelpdeskWidget.js':
                $code = $this->getHdWidget();
                break;
            default:
                $code = '';
        }

        $res = new Response($code, 200, ['Content-Type' => 'application/javascript']);
        $res->setTtl(3600);
        $res->setEtag(sha1($code));
        $res->setPublic();
        $res->isNotModified($this->request);
        $res->sendHeaders();
        $res->sendContent();
        exit;
    }

    private function getChatWidget()
    {
        $baseUrl   = $this->request->getUriForPath('/');
        $loaderSrc = $this->request->getUriForPath('/assets/'.$this->env->getAppName().'/pub/build/widget_loader.min.js');

        $options = [
            'helpdeskUrl' => $baseUrl,
            'widget'      => [
                'type'     => 'column',
                'position' => 'right',
            ],
            'button' => [
                'translations' => [['language' => 1,
                                    'name'     => 'Help',
                                   ]],
                'size'   => 'medium',
                'colors' => [
                    'background' => '#62ad8c',
                    'text'       => '#ffffff',
                    'border'     => '#4e9576',
                ],
            ],
            'chat' => [
                'request_user_info' => false,
                'proactive'         => false,
                'popup'             => [
                    'translations' => [[
                                           'language' => 1,
                                           'title'    => 'Customer Support',
                                           'message'  => 'Need help? Just reply to start a live chat with one of our team.',
                                       ]],
                    'reply_type' => 'buttons',
                ],
                'begin_mode'      => 'form',
                'waiting_timeout' => 150,
            ],
            'ticket' => ['select_department' => 'custom'],
        ];

        // override options
        $options = json_encode($options);

        return <<<CODE
(function() {
window.DESKPRO_WIDGET_OPTIONS = $options;

var scr   = document.createElement('script');
scr.type  = 'text/javascript';
scr.async = true;
scr.src   = '$loaderSrc';
(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);
})();
CODE;
    }

    private function getFormWidget()
    {
        $loaderSrc = $this->request->getUriForPath('/assets/'.$this->env->getAppName().'/pub/build/embed_loader.min.js');

        return <<<CODE
(function() {
window.DESKPRO_EMBED_OPTIONS = {
    "helpdeskUrl": DpNewTicket_Options.deskproUrl.replace(/\/$/, ''),
    "containerId": DpNewTicket_Options.containerId,
    "department": DpNewTicket_Options.departmentId,
    "type": "form",
    "language": "",
    "width": 0
};

document.getElementById(DpNewTicket_Options.containerId).style.display = 'block';

var scr   = document.createElement('script');
scr.type  = 'text/javascript';
scr.async = true;
scr.src   = '$loaderSrc';
(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);
})();
CODE;
    }

    /**
     * @return string
     */
    private function getHdWidget()
    {
        $loaderSrc = $this->request->getUriForPath('/assets/'.$this->env->getAppName().'/pub/build/embed_loader.min.js');

        return <<<CODE
(function() {
window.DESKPRO_EMBED_OPTIONS = {
    "helpdeskUrl": DpHelpdesk_Options.deskproUrl.replace(/\/$/, ''),
    "containerId": DpHelpdesk_Options.containerId,
    "type": "helpdesk",
    "language": DpHelpdesk_Options.languageId,
    "width": 0,
    "minHeight": DpHelpdesk_Options.initialHeight,
    "loadPath": DpHelpdesk_Options.loadPath,
    "ticketFormDefaults": DpHelpdesk_Options.ticketFormDefaults
};

document.getElementById(DpHelpdesk_Options.containerId).style.display = 'block';

var scr   = document.createElement('script');
scr.type  = 'text/javascript';
scr.async = true;
scr.src   = '$loaderSrc';
(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);
})();
CODE;
    }

    //###################################################################################################################
    // Dynamic asset
    //###################################################################################################################

    private function serveDynAsset($asset)
    {
        if ($asset === 'inst_info.js') {
            $cb = !empty($_GET['callback']) ? $_GET['callback'] : 'dp_load_version_cb';

            $res = new Response('', 200, ['Content-Type' => 'application/javascript']);
            $res->setTtl(3600);
            $res->setEtag(sha1('inst_info.js'.$this->env->getAppName().$cb));
            $res->setPublic();
            $res->isNotModified($this->request);

            if (!$res->isNotModified($this->request)) {
                $info = [
                    'helpdeskUrl' => rtrim($this->request->getUriForPath('/'), '/'),
                    'assetUrl'    => rtrim($this->request->getUriForPath('/assets/'.$this->env->getAppName()), '/'),
                    'buildId'     => $this->env->getAppName(),
                ];
                if ($assetRoot = $this->env->getConfig('paths.assets_root')) {
                    $info['assetUrl'] = $assetRoot;
                }
                $cb   = preg_replace('#[^a-zA-Z0-9_\.\-]#', '', $cb);
                $code = "$cb(".json_encode($info).')';
                $res->setContent($code);
            }

            $res->sendHeaders();
            $res->sendContent();
            exit;
        }

        $assetPath = realpath($this->env->getAppWwwAssetDir().'/'.$asset);

        // Invalid path, not in the dir we expected (maybe user supplied ..'s in the url)
        // Or it just doesnt exist
        try {
            $asset = new File($assetPath);
        } catch (FileNotFoundException $e) {
            return;
        }

        $res = $this->getResponseForFile($asset);
        $res->setTtl(300);
        $res->setPublic();
        $res->isNotModified($this->request);
        $res->sendHeaders();
        $res->sendContent();
        exit;
    }

    /**
     * @param File $file
     *
     * @return Response
     */
    private function getResponseForFile(File $file)
    {
        switch ($file->getExtension()) {
            case 'js':
                $contentType = 'text/javascript';
                break;
            case 'css':
                $contentType = 'text/css';
                break;
            case 'woff':
                $contentType = 'application/x-font-woff';
                break;
            case 'woff2':
                $contentType = 'application/x-font-woff2';
                break;
            case 'ttf':
                $contentType = 'application/x-font-ttf';
                break;
            default:
                $contentType = Psr7\mimetype_from_extension($file->getExtension());
        }

        if (!$contentType) {
            return new Response('', 404);
        }

        $res = new Response('', 200, ['Content-Type' => $contentType]);
        $res->setEtag(sha1($file->getRealPath()));
        $res->setPublic();
        $res->setLastModified(new \DateTime('@'.$file->getMTime()));
        $res->setTtl(300);
        $res->headers->set('Content-Length', $file->getSize());
        $res->headers->set('Access-Control-Allow-Origin', '*');
        $res->headers->set('Access-Control-Allow-Credentials', 'true');
        $res->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $res->headers->set('Access-Control-Allow-Headers', 'DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type');

        if ($this->request->getMethod() === 'OPTIONS') {
            $res->headers->set('Content-Length', '0');
            $res->setContent('');

            return $res;
        }

        $res->headers->makeDisposition('inline', $file->getBasename());

        if (!$res->isNotModified($this->request)) {
            $res->setContent(file_get_contents($file->getRealPath()));
        }

        return $res;
    }
}
