<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * Class ApiCombinerController.
 *
 * @ApiModes("all")
 */
class ApiCombinerController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new PassPermission();
    }

    public function getAction()
    {
        $data = [];

        $returned_data = [];

        foreach ($this->in->getCleanValueArray('load_data', 'string', 'string') as $k => $load_data_id) {

            // Cut out everything before the /api/ which will could be the base-path
            $load_data_id = preg_replace('#^(.*?)\/api\/#', '/api/', $load_data_id);

            // Cut off the query string
            $req_data = [];
            if (($q_pos = strpos($load_data_id, '?')) !== false) {
                list($load_data_id, $qs) = explode('?', $load_data_id, 2);
                parse_str($qs, $req_data);
            }

            try {
                $route_info = $this->container->getRouter()->match($load_data_id);
            } catch (\Exception $e) {
                $route_info = null;
            }

            $ctrl_name = null;
            $ctrl_path = $route_info['_controller'];
            $m         = null;
            if (preg_match('#^(Application|Cloud)\\\\(.*?)\\\\Controller\\\\(.*?)Controller::(.*?)Action$#', $ctrl_path, $m)) {
                $ctrl_name = $m[2].':'.$m[3].':'.$m[4];
                if ($m[1] == 'Cloud') {
                    $ctrl_name = 'Cloud'.$ctrl_name;
                }
            }

            $route_id = $route_info['_route'];
            unset($route_info['_controller']);
            unset($route_info['_route']);
            $path_vars = $route_info;

            $load_data = null;
            if ($ctrl_name) {
                if ($req_data) {
                    foreach ($req_data as $rk => $rv) {
                        $_REQUEST[$rk] = $rv;
                        $_GET[$rk]     = $rv;
                    }
                    $this->in->resetSources();
                }

                $load_data = $this->forward($ctrl_name, $path_vars)->getContent();
                $load_data = @json_decode($load_data);

                if ($req_data) {
                    foreach ($req_data as $rk => $rv) {
                        unset($_REQUEST[$rk]);
                        unset($_GET[$rk]);
                    }
                    $this->in->resetSources();
                }
            }

            if ($load_data !== null) {
                $save_key = $k;
                if (is_numeric($save_key)) {
                    $save_key = $route_id;
                }
                $returned_data[] = $save_key;
                $data[$save_key] = $load_data;
            }
        }

        $data['returned_data'] = $returned_data;

        return $this->createApiResponse($data);
    }
}
