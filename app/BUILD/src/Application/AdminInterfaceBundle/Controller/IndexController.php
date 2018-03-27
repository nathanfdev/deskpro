<?php

/**
 * DeskPRO.
 */

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\App\Assets\RequireJsConfigGenerator as AppsRequireJsConfigGenerator;
use Application\DeskPRO\Entity\ApiToken;
use DpSys\License;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    public function redirectToAdminAction()
    {
        $to = str_replace("'", "\\'", $this->generateUrl('agent').'#admin:/');

        $html = <<<HTML
<html>
<head>
<script>
var target = '{$to}';
if (window.location.hash && window.location.hash.length) {
    target += window.location.hash.substring(1).replace(/^\//, '');
}
window.location = target;
</script>
</head>
</html>
HTML;

        return new Response($html);
    }

    public function interfaceAction()
    {
        $token               = new ApiToken();
        $token->scope        = ApiToken::SCOPE_SESSION;
        $token->person       = $this->person;
        $token->date_expires = new \DateTime('+1 hour');

        $this->em->persist($token);
        $this->em->flush();

        // Default help states
        $help_states = $this->db->fetchAllKeyValue("
            SELECT name, value_str
            FROM people_prefs
            WHERE name LIKE 'inhelp.%'
        ");

        $inhelp_states = [];
        foreach ($help_states as $k => $v) {
            $k                 = preg_replace('#^inhelp\.#', '', $k);
            $inhelp_states[$k] = $v;
        }

        $rjs_apps = new AppsRequireJsConfigGenerator(
            $this->container->getAppManager(),
            $this->generateUrl('serve_file_root').'/apps',
            false
        );
        $rjs_apps_config = $rjs_apps->generateRequireJsConfigCode();

        $is_first_load = !$this->settings->get('admin_has_loaded');
        if ($is_first_load) {
            $this->settings->setSetting('admin_has_loaded', 1);
        }

        return $this->render('AdminInterfaceBundle:Index:interface.html.twig', [
            'api_token'             => $token,
            'session'               => $this->session->getEntity(),
            'initial_request_token' => $this->session->generateSecurityToken('request_token', 600),
            'inhelp_states'         => $inhelp_states,
            'rjs_apps_config'       => $rjs_apps_config,
            'redirect_license'      => defined('DP_BILLING_ERROR'),
            'license_server'        => rtrim(License::getSecureLicServer(), '/'),
            'is_first_load'         => $is_first_load,
        ]);
    }
}
