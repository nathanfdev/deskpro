<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\Translate\JsExporter;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InterfaceController extends AbstractController
{
    ####################################################################################################################
    # load-view
    ####################################################################################################################

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
            $rendered = "<script type=\"application/json\" class=\"DP_LOAD_DATA\">".$load_data."</script>$rendered";
        }

        return $this->createResponse($rendered);
    }

    ####################################################################################################################
    # multi-load-view
    ####################################################################################################################

    public function multiLoadViewAction()
    {
        $views = array();

        foreach ($this->in->getCleanValueArray('views', 'string', 'discard') as $view_name) {
            $id       = $view_name;
            $tpl_name = $this->getRealViewName($view_name);

            $rendered = null;
            $rendered = $this->renderTemplateView($tpl_name);

            $views[] = array(
                'id'       => $id,
                'template' => $tpl_name,
                'source'   => $rendered,
            );
        }

        return $this->createJsonResponse($views);
    }

    ####################################################################################################################
    # load-lang
    ####################################################################################################################

    public function loadLangAction($_format)
    {
        $js_exporter = new JsExporter($this->container->getTranslator());

        $get_phrases = include DP_ROOT.'/languages/expose-js.php';
        $get_phrases = $get_phrases['admin'];

        if ($_format == 'js') {
            $varname = 'DP_LANG';
            if ($this->in->getString('varname')) {
                $varname = $this->in->getString('varname');
            }

            $res = new Response(
                $js_exporter->exportToJsFile($varname, $get_phrases),
                200,
                array('Content-Type' => 'text/javascript')
            );
        } else {
            $res = new Response(
                $js_exporter->exportToJson($get_phrases),
                200,
                array('Content-Type' => 'application/json')
            );
        }

        return $res;
    }

    ####################################################################################################################

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
                    if ($real_path && strpos($real_path, $native_package->getNativeDir()) === 0 && file_exists($real_path)) {
                        return file_get_contents($real_path);
                    }
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
                return array(
                    'default_portal_style' => $this->settings->getDefaultGroup('user_style', false),
                );
            default:
                return array();
        }
    }


    /**
     * @param $code
     * @return BinaryFileResponse
     */
    public function downloadExportFileAction($code)
    {
        if (!$data = $this->em->getRepository('DeskPRO:TmpData')->getByCode($code)) {
            throw new NotFoundHttpException;
        }

        $file = $data->getData('file');

        if (!file_exists($file)) {
            throw new NotFoundHttpException;
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
