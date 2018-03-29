<?php

/**
 * DeskPRO.
 */

namespace Application\AdminInterfaceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class UpgradeController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        if (defined('DPC_IS_CLOUD')) {
            throw $this->createNotFoundException();
        }

        return parent::preActionHandler($request, $action, $arguments);
    }

    //###################################################################################################################
    // index
    //###################################################################################################################

    public function indexAction()
    {
        if ($this->container->getSetting('disable_admin_deskpro_updates')) {
            return $this->redirectRoute('admin');
        }

        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        if ($DP_ENV->getDatManager()->hasTxtFile('server_info_auth')) {
            $auth = $DP_ENV->getDatManager()->readTxtFile('server_info_auth');
        } else {
            $auth = 'VIEWER';
        }

        return $this->redirectRoute('admin_upgrade_view', ['auth' => $auth]);
    }
}
