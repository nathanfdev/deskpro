<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Request;

/**
 * Docs API Controller.
 *
 * @ApiModes("all")
 */
class DocsController extends AbstractController
{
    protected function init()
    {
        $this->em       = $this->get('doctrine.orm.entity_manager');
        $this->db       = $this->get('database_connection');
        $this->in       = $this->get('deskpro.core.input_reader');
        $this->cleaner  = $this->get('deskpro.core.input_cleaner');
        $this->settings = $this->get('deskpro.core.settings');
    }

    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        return;
    }

    //###################################################################################################################
    // about
    //###################################################################################################################

    public function aboutAction()
    {
        return $this->render('LegacyApiBundle:SwaggerUi:about.html.twig');
    }

    //###################################################################################################################
    // api
    //###################################################################################################################

    public function apiAction()
    {
        return $this->render('LegacyApiBundle:SwaggerUi:api.html.twig');
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction()
    {
        return $this->serveResource('deskpro-api');
    }

    //###################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        return $this->serveResource($id);
    }

    //###################################################################################################################
    // get-agents-for-key
    //###################################################################################################################

    public function getAgentsForKeyAction()
    {
        $apikey = $this->em->getRepository('DeskPRO:ApiKey')->findByKeyString($this->in->getString('key'));
        if ($apikey) {
            if ($apikey->isFlagSet('super')) {
                $agents = $this->container->getAgentData()->getNames();
            } else {
                $agents                      = [];
                $agents[$apikey->person->id] = $apikey->person->getDisplayName();
            }
            $default_id = $apikey->person ? $apikey->person->id : 0;
        } else {
            $agents     = [];
            $default_id = 0;
        }

        return $this->createJsonResponse([
            'names'      => $agents,
            'default_id' => $default_id,
        ]);
    }

    //###################################################################################################################

    private function getResourcePath($res)
    {
        return DP_ROOT.'/src/Application/LegacyApiBundle/Resources/views/SwaggerDocs/'.ltrim($res, '/').'.json';
    }

    private function serveResource($res)
    {
        $path = $this->getResourcePath($res);
        if (!file_exists($path)) {
            throw $this->createNotFoundException();
        }

        return $this->createJsonResponse(file_get_contents($path));
    }
}
