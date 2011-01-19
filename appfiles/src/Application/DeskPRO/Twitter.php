<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO;

use Zend_Oauth_Consumer;
use Zend_Http_Client;
/**
 * A class that extends Zend_OAuth
 * and manages connecting and calling Twitter APIs
 */
class Twitter
{
        //DeskPro Twitter App configuration Array
        private $_config = array(
                'callbackUrl' => 'http://basiltest.dyndns.biz/dp/DeskPRO/index_dev.php/agent/twitter/callback',
                'siteUrl' => 'http://twitter.com/oauth',
                'consumerKey' => 'MmuQ3021xYehoBzjjd3WFg',
                'consumerSecret' => 'RORtnJhEUkesx7jDZCSHXonIRjLVAeQ9hIXK8MBf9o'
            );

        function __construct() {
        }

        /*
        * Redirects app to the twitter App authentication page
        *
        * Once authenticated successfully, Redirects back to the callback URL registerd with Twitter
        *
        * If Already authenticated, Returns true to the calling function
        */
        function requestAuth(){
                $consumer = new Zend_Oauth_Consumer($this->_config);
                /*
                * Check for already authenticated and
                * app has TWITTER ACCESS TOKEN
                */
                if (!isset($_SESSION['TWITTER_ACCESS_TOKEN'])) {
                    /*
                    * Redirect to twitter API with REQUEST TOKEN
                    */
                    $token = $consumer->getRequestToken();
                    $_SESSION['TWITTER_REQUEST_TOKEN'] = serialize($token);
                    $consumer->redirect();die;
                }else{
                    $this->getFavourites();
                    return true;
                }
        }

        /*
        * To handle callback from Twitter API
        *
        * @param Array $this->config --- Contains configuration of Twitter client
        */
        function handleCallback(){
                $consumer = new Zend_Oauth_Consumer($this->_config);

                if (!empty($_GET) && isset($_SESSION['TWITTER_REQUEST_TOKEN'])) {
                    $token = $consumer->getAccessToken($_GET, unserialize($_SESSION['TWITTER_REQUEST_TOKEN']));
                    $_SESSION['TWITTER_ACCESS_TOKEN'] = serialize($token);
                    $_SESSION['logged_in'] = 1;
                    setcookie('access_token',serialize($token));
                    die;
                }
        }

        function getFavourites(){
            if(!isset($_SESSION['TWITTER_ACCESS_TOKEN'])){
                $this->requestAuth();
            }
            $token = unserialize($_SESSION['TWITTER_ACCESS_TOKEN']);
            $token = (object)$token;

            $client = $token->getHttpClient($this->_config);
            $client->setUri('http://api.twitter.com/1/favorites.json');
            $client->setMethod(Zend_Http_Client::GET);

            $response = $client->request();
          print_r(json_decode($response->getBody()));die;
            return json_decode($response->getBody());
        }


        function getFollowersByHandle($screen_name){
            if(!isset($_SESSION['TWITTER_ACCESS_TOKEN'])){
                $this->requestAuth();
            }

            $token = unserialize($_SESSION['TWITTER_ACCESS_TOKEN']);
            $token = (object)$token;

            $client = $token->getHttpClient($this->_config);
            
            $client->setUri('http://twitter.com/statuses/followers.json');
            $client->setParameterGet('screen_name', $screen_name);
            $client->setMethod(Zend_Http_Client::GET);
            $response = $client->request();
            print_r(json_decode($response->getBody()));die;
            return json_decode($response->getBody());
        }
}
