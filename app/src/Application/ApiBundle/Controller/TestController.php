<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;
use Application\DeskPRO\App;

/**
 * A Test API resource
 */
class TestController extends AbstractController
{
    public function preAction($action, $arguments = null)
    {
        if ($action == 'testAction' || $action == 'aboutAction') {
            return null;
        }

        return parent::preAction($action, $arguments);
    }


    /**
     * This action simply returns a message to indicate that the API is working
     *
     * @depreciated
     */
    public function testAction()
    {
        $request = $this->container->getRequest();

        $api_url = App::getSetting('core.deskpro_url');
        $api_url .= 'index.php/';

        // If this call is secure, then we know https works and the client
        // requested it specifically, so return the same protocol
        if ($request->isSecure() && strpos($api_url, 'https://') !== 0 && !defined('DPC_IS_CLOUD')) {
            $api_url = preg_replace('#^http://#', 'https://', $api_url);
        }

        return $this->createApiResponse(array(
            'success'     => true,
            'api_version' => DP_BUILD_TIME,
            'api_url'     => $api_url
        ));
    }


    /**
     * This returns info about the helpdesk. It's meant to verify the existence of DeskPRO (eg mobile app)
     * and give the API endpoint.
     */
    public function discoverAction()
    {
        $request = $this->container->getRequest();

        $data = array();
        $data['helpdesk_url'] = App::getSetting('core.deskpro_url');
        $data['helpdesk_url'] = str_replace('/index.php', '', $data['helpdesk_url']);
        $data['helpdesk_url'] = rtrim($data['helpdesk_url'], '/') . '/';

        $url_info = @parse_url($data['helpdesk_url']);
        $data['helpdesk_path'] = @$url_info['path'];

        $data['api_url'] = $data['helpdesk_url'] . '/index.php/api/';

        // If this request itself is secure then we know ssl works
        // so we sholud prefer it
        if ($request->isSecure()) {
            $data['api_url'] = preg_replace('#^http://#', 'https', $data['api_url']);
        }

        $url_info = @parse_url($data['api_url']);
        $data['api_path'] = @$url_info['path'];

        $data['api_version'] = DP_BUILD_TIME;

        return $this->createApiResponse($data);
    }

    /**
     * Another test action to indicate the POST API is working
     */
    public function postTestAction()
    {
        $message = isset($_POST['message']) ? $_POST['message'] : 'Post works!';

        return $this->createApiResponse(array('message' => $message));
    }
}
