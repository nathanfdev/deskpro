<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;
use \Orb\Util\Util;
use Zend_Service_Twitter;
use Zend_Oauth_Consumer;
class TwitterController extends AbstractController
{
        private $_config = array(
                'callbackUrl' => 'http://basiltest.dyndns.biz/dp/DeskPRO/index_dev.php/admin/twitter/addaccount',
                'siteUrl' => 'http://twitter.com/oauth',
                'consumerKey' => 'MmuQ3021xYehoBzjjd3WFg',
                'consumerSecret' => 'RORtnJhEUkesx7jDZCSHXonIRjLVAeQ9hIXK8MBf9o'
            );
	public function indexAction()
	{
                return $this->render('AdminBundle:Twitter:accounts.twig.html');
	}


        public function authenticateAction()
        {
                $twitter = new Zend_Service_Twitter($this->_config);
                $token = $twitter->getRequestToken();
                $this->session->set('twitter_request_token', serialize($token));
                $twitter->redirect();die;
        }



        public function addaccountAction()
        {
                $twitter = new Zend_Service_Twitter($this->_config);
                $token = $twitter->getAccessToken($_GET, unserialize($this->session->get('twitter_request_token')));
                echo "<pre>";print_r($_GET);echo $token;
                $params = $token;
                $components = explode("screen_name=",$params);
                $screen_name = $components[1];
                $entry = 0;//App::getEntityRepository('DeskPRO:TwitterAccount')->find($screen_name);
                if (!$entry) {
                        $twit_acc = new Entity\TwitterAccount();
                        $twit_acc['twitter_handle'] = $screen_name;
                        $twit_acc['access_token'] = serialize($token);

                        App::getOrm()->persist($twit_acc);
                        App::getOrm()->flush();
                }
                echo $screen_name;die;
        }

}