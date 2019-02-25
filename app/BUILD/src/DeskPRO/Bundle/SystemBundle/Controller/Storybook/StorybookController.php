<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Controller\Storybook;

use DeskPRO\Component\Filesystem\SafeFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StorybookController.
 */
class StorybookController extends Controller
{
    /**
     * @Route("/ui/storybook/{path}", requirements={"path"=".+"})
     */
    public function storybookAction(Request $request, $path = '')
    {
        if (!$path) {
            return $this->redirect($request->getRequestUri().'/index.html');
        }

        return new Response(
            SafeFile::file_get_contents(DP_WEB_ROOT.'/pub/build/storybook/'.$path, DP_WEB_ROOT.'/pub/build/storybook/'),
            Response::HTTP_OK,
            ['Content-Type' => stripos(strrev($path), strrev('.js')) === 0 ? 'application/javascript' : 'text/html']
        );
    }

    /**
     * @Route("/ui/assets-path")
     */
    public function assetsPathAction()
    {
        /** @var \DeskPRO\Bundle\PortalBundle\Designer\AssetsManager $assets */
        $assets = $this->get('templating.email.twig.extension.assets');
        $path   = $assets->getAssetUrl('', 'app_assets');

        return new Response($path);
    }
}
