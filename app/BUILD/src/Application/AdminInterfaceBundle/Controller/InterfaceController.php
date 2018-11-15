<?php

/**
 * DeskPRO.
 */

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\Translate\JsExporter;
use DeskPRO\Component\Filesystem\SafeFile;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InterfaceController extends AbstractController
{
    //###################################################################################################################
    // load-view
    //###################################################################################################################

    public function loadViewAction($view_name)
    {
        $load_data = null;

        // Load data from a route at the same time
        if ($this->in->getString('load_data')) {
            try {
                $route_info = $this->container->getRouter()->match($this->in->getString('load_data'));
            } catch (\Exception $e) {
                $route_info = null;
            }

            if ($route_info) {
                $ctrl_path = $route_info['_controller'];
                unset($route_info['_controller']);
                unset($route_info['_route']);
                $path_vars = $route_info;

                if ($ctrl_path) {
                    $load_data = $this->forward($ctrl_path, $path_vars)->getContent();
                }
            }
        }

        $tpl_name = $this->getRealViewName($view_name);

        $rendered = $this->renderTemplateView($tpl_name);

        if ($load_data) {
            $rendered = '<script type="application/json" class="DP_LOAD_DATA">'.$load_data."</script>$rendered";
        }

        return $this->createResponse($rendered);
    }

    //###################################################################################################################
    // multi-load-view
    //###################################################################################################################

    public function multiLoadViewAction()
    {
        $views = [];

        foreach ($this->in->getCleanValueArray('views', 'string', 'discard') as $view_name) {
            $id       = $view_name;
            $tpl_name = $this->getRealViewName($view_name);

            $rendered = null;
            $rendered = $this->renderTemplateView($tpl_name);

            $views[] = [
                'id'       => $id,
                'template' => $tpl_name,
                'source'   => $rendered,
            ];
        }

        return $this->createJsonResponse($views);
    }

    //###################################################################################################################
    // load-lang
    //###################################################################################################################

    public function loadLangAction($_format)
    {
        $js_exporter = new JsExporter($this->container->getTranslator());

        $get_phrases = include DP_ROOT.'/locales/expose-js.php';
        $get_phrases = $get_phrases['admin'];

        if ($_format == 'js') {
            $varname = 'DP_LANG';
            if ($this->in->getString('varname')) {
                $varname = $this->in->getString('varname');
            }

            $res = new Response(
                $js_exporter->exportToJsFile($varname, $get_phrases),
                200,
                ['Content-Type' => 'text/javascript']
            );
        } else {
            $res = new Response(
                $js_exporter->exportToJson($get_phrases),
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return $res;
    }

    //###################################################################################################################

    private function renderTemplateView($tpl_name)
    {
        $m = null;

        if ($this->tpl->exists($tpl_name)) {
            return $this->renderView($tpl_name, $this->getViewParams($tpl_name));
        } elseif (preg_match('#^Apps:(.*?):(.*?)$#', $tpl_name, $m)) {
            $app_name = $m[1];
            $tpl_name = $m[2];

            $app_manager = $this->container->getAppManager();
            if ($app_manager->isPackageInstalled($app_name)) {
                $package = $app_manager->getPackage($app_name);
                if ($package->native_name) {
                    $native_package = $app_manager->getNativePackageConfig($package);

                    $real_path = @realpath($native_package->getNativeDir().'/Resources/views/'.$tpl_name);

                    return SafeFile::fileGetContents($real_path, [$native_package->getNativeDir()]);
                }
            }
        }

        return '';
    }

    private function getRealViewName($view_name)
    {
        if (strpos($view_name, 'Apps:') === 0) {
            $tpl_name = $view_name;
        } else {
            $view_name = preg_replace('#[^a-zA-Z0-9_\-/\.:]#', '', $view_name);
            $view_name = Strings::strReplaceOne('/', ':', $view_name);
            $view_name = str_replace('.html', '.html.twig', $view_name);
            $view_name = str_replace('.html.twig.twig', '.html.twig', $view_name);
            $tpl_name  = "AdminInterfaceBundle:$view_name";
        }

        if (defined('DPC_IS_CLOUD')) {
            if ($this->tpl->exists('Cloud'.$tpl_name)) {
                $tpl_name = 'Cloud'.$tpl_name;
            }
        }

        return $tpl_name;
    }

    private function getViewParams($tpl_name)
    {
        switch ($tpl_name) {
            case 'AdminInterfaceBundle:PortalEditor:frame.html.twig':
                return [
                    'default_portal_style' => $this->settings->getDefaultGroup('user_style', false),
                ];
            default:
                return [];
        }
    }

    /**
     * @param $code
     *
     * @return BinaryFileResponse
     */
    public function downloadExportFileAction($code)
    {
        if (!$data = $this->em->getRepository('DeskPRO:TmpData')->getByCode($code)) {
            throw new NotFoundHttpException();
        }

        $file = $data->getData('file');

        if ($data->getData('url')) {
            $response = new RedirectResponse($data->getData('url'));

            return $response;
        }

        if (!file_exists($file)) {
            throw new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($file);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            pathinfo($file, PATHINFO_BASENAME),
            iconv('UTF-8', 'ASCII//TRANSLIT', 'DP_Export.csv')
        );

        return $response;
    }
}
