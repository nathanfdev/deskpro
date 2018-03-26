<?php

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Request;

/**
 * A Test API resource.
 *
 * @ApiModes("all")
 */
class TestController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        if ($action == 'testAction' || $action == 'aboutAction') {
            return;
        }

        return parent::preActionHandler($request, $action, $arguments);
    }

    /**
     * This action simply returns a message to indicate that the API is working.
     *
     * @deprecated
     */
    public function testAction()
    {
        $request = $this->container->getRequest();

        $api_url = $this->container->getBrandSetting('core.deskpro_url');
        $api_url .= 'index.php/';

        // If this call is secure, then we know https works and the client
        // requested it specifically, so return the same protocol
        if ($request->isSecure() && strpos($api_url, 'https://') !== 0 && !defined('DPC_IS_CLOUD')) {
            $api_url = preg_replace('#^http://#', 'https://', $api_url);
        }

        return $this->createApiResponse([
            'success'     => true,
            'api_version' => DP_BUILD_TIME,
            'api_url'     => $api_url,
        ]);
    }

    /**
     * This returns info about the helpdesk. It's meant to verify the existence of DeskPRO (eg mobile app)
     * and give the API endpoint.
     */
    public function discoverAction()
    {
        $request = $this->container->getRequest();

        $data                 = [];
        $data['helpdesk_url'] = $this->container->getBrandSetting('core.deskpro_url');
        $data['helpdesk_url'] = str_replace('/index.php', '', $data['helpdesk_url']);
        $data['helpdesk_url'] = rtrim($data['helpdesk_url'], '/').'/';

        $url_info              = @parse_url($data['helpdesk_url']);
        $data['helpdesk_path'] = @$url_info['path'];

        $data['api_url'] = $data['helpdesk_url'].'/index.php/api/';

        // If this request itself is secure then we know ssl works
        // so we sholud prefer it
        if ($request->isSecure()) {
            $data['api_url'] = preg_replace('#^http://#', 'https', $data['api_url']);
        }

        $url_info         = @parse_url($data['api_url']);
        $data['api_path'] = @$url_info['path'];

        $data['api_version'] = DP_BUILD_TIME;

        return $this->createApiResponse($data);
    }

    /**
     * Another test action to indicate the POST API is working.
     */
    public function postTestAction()
    {
        $message = isset($_POST['message']) ? $_POST['message'] : 'Post works!';

        return $this->createApiResponse(['message' => $message]);
    }
}
