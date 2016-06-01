<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpSys\Boot\BootTask;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

        if (substr($path, 0, 12) === '/dyn-assets/') {
            if (
                strpos($path, 'widget_loader.js') || strpos($path, 'widget_loader.min.js')
                || strpos($path, 'embed_loader.js') || strpos($path, 'embed_loader.min.js')
            ) {
                return preg_replace('#^/dyn\-assets/#', '', $path);
            }
        }

        return;
    }

    ####################################################################################################################
    # Legacy widgets
    ####################################################################################################################

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

        $options = array(
            'helpdeskUrl' => $baseUrl,
            'widget'      => array(
                'type'     => 'column',
                'position' => 'right',
            ),
            'button' => array(
                'translations' => array(array('language' => 1,
                        'name'                           => 'Help',
                )),
                'size'   => 'medium',
                'colors' => array(
                    'background' => '#62ad8c',
                    'text'       => '#ffffff',
                    'border'     => '#4e9576',
                ),
            ),
            'chat' => array(
                'request_user_info' => true,
                'proactive'         => false,
                'popup'             => array(
                    'translations' => array(array(
                        'language' => 1,
                        'title'    => 'Customer Support',
                        'message'  => 'Need help? Just reply to start a live chat with one of our team.',
                    )),
                    'reply_type' => 'buttons',
                ),
                'begin_mode'      => 'form',
                'waiting_timeout' => 150,
            ),
            'ticket' => array('select_department' => 'custom'),
        );

        // override options
        $options = json_encode($options);

        return "<!--DESKPRO_WIDGET_LOADER::BEGIN-->\n<script type=\"text/javascript\">\nwindow.DESKPRO_WIDGET_OPTIONS = $options;\n</script>\n<script type=\"text/javascript\" src=\"$loaderSrc\"></script>\n<!--DESKPRO_WIDGET_LOADER::END-->";
    }

    private function getFormWidget()
    {
        $loaderSrc = $this->request->getUriForPath('/assets/'.$this->env->getAppName().'/pub/build/embed_loader.min.js');

        return <<<CODE
(function() {
window.DESKPRO_EMBED_OPTIONS = {
    "helpdeskUrl": DpHelpdesk_Options.deskproUrl.replace(/\/$/, ''),
    "containerId": DpHelpdesk_Options.containerId,
    "department": DpHelpdesk_Options.departmentId,
    "type": "form",
    "language": "",
    "width": 0
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

    private function getHdWidget()
    {
        $loaderSrc = $this->request->getUriForPath('/assets/'.$this->env->getAppName().'/pub/build/embed_loader.min.js');

        return <<<CODE
(function() {
window.DESKPRO_EMBED_OPTIONS = {
    "helpdeskUrl": DpHelpdesk_Options.deskproUrl.replace(/\/$/, ''),
    "containerId": DpHelpdesk_Options.containerId,
    "type": "helpdesk",
    "language": "",
    "width": 0
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

    ####################################################################################################################
    # Dynamic asset
    ####################################################################################################################

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
        if (!$assetPath || strpos($assetPath, $this->env->getAppWwwAssetDir()) !== 0 || !is_file($assetPath)) {
            return;
        }

        $res = $this->getResponseForFile($assetPath);
        $res->setTtl(300);
        $res->setPublic();
        $res->isNotModified($this->request);
        $res->sendHeaders();
        $res->sendContent();
        exit;
    }

    /**
     * @param string $filePath
     *
     * @return BinaryFileResponse
     */
    private function getResponseForFile($filePath)
    {
        $ext = substr($filePath, strpos($filePath, '.') + 1);

        switch ($ext) {
            case 'js': $contentType = 'application/javascript'; break;
            default:   $contentType = 'text/plain'; break;
        }

        $res = new Response('', 200, ['Content-Type' => $contentType]);
        $res->setEtag(sha1($filePath));
        $res->setPublic();
        $res->setLastModified(new \DateTime('@'.filemtime($filePath)));
        $res->setTtl(300);
        $res->headers->set('Content-Length', filesize($filePath));
        $res->headers->makeDisposition('inline', basename($filePath));

        if (!$res->isNotModified($this->request)) {
            $res->setContent(file_get_contents($filePath));
        }

        return $res;
    }
}
