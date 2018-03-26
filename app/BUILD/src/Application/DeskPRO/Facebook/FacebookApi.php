<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Facebook;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\FacebookApp;
use Application\DeskPRO\Entity\FacebookPage;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FacebookApi
{
    /**
     * @var HttpClient
     */
    protected $facebook;

    /**
     * @var FacebookApp
     */
    protected $app;

    /**
     * @var App ID
     */
    protected $app_id;

    /**
     * @var App secret
     */
    protected $app_secret;

    /**
     * @var string app token using for this request
     */
    protected $app_token;

    public function __construct(FacebookApp $app = null, $app_id = null, $app_secret = null)
    {
        if ($app) {
            $this->app_id     = $app->app_id;
            $this->app_secret = $app->app_secret;
        } else {
            $this->app_id     = $app_id;
            $this->app_secret = $app_secret;
        }
    }

    public function getClient()
    {
        if ($this->facebook) {
            return $this->facebook;
        }

        $this->facebook = new HttpClient(['base_uri' => 'https://graph.facebook.com']);

        if (!$this->app_token) {
            $output = $this->sendGetRequest(
                '/oauth/access_token', [
                    'client_id'     => $this->app_id,
                    'client_secret' => $this->app_secret,
                    'grant_type'    => 'client_credentials',
                ]
            );

            $this->app_token = $output['access_token'];
        }

        return $this->facebook;
    }

    /**
     * The short-term token needs to be extended here for use/storage on server.
     *
     * If the token received is not used for 60 days, it expires and user must re-setup their channel
     *
     * @param FacebookPage $page
     *
     * @return bool true if valid user token is now in $page
     */
    public function extendUserToken(FacebookPage $page, $extend_page_token = true)
    {
        $output = $this->sendGetRequest(
            '/oauth/access_token', [
                'grant_type'        => 'fb_exchange_token',
                'client_id'         => $this->app_id,
                'client_secret'     => $this->app_secret,
                'fb_exchange_token' => $page->user_token,
            ]
        );

        if ($output) {
            $page->user_token               = $output['access_token'];
            $page->date_user_token_received = new \DateTime('now');
        }

        if ($extend_page_token) {
            $this->extendPageToken($page, false);
        }

        return true;
    }

    /**
     * The short-term page token needs to be extended here for use/storage on server.
     *
     * @param FacebookPage $page
     *
     * @return bool true if valid user token is now in $page
     */
    public function extendPageToken(FacebookPage $page, $extend_user_token = true)
    {
        if ($extend_user_token) {
            $this->extendUserToken($page, false);
        }

        $output = $this->sendGetRequest(
            '/me/accounts', [
                'access_token' => $page->user_token,
            ]
        );

        if ($output) {
            foreach ($output['data'] as $page_info) {
                if ($page->graph_id == $page_info['id']) {
                    $page->page_token = $page_info['access_token'];
                    break;
                }
            }
        }

        return true;
    }

    public function commentOnPost($graph_id, $message, $token)
    {
        $output = $this->sendPostRequest(
            sprintf('/%s/comments', $graph_id),
            [
                'app_id'       => $this->app_id,
                'access_token' => $token,
                'message'      => $message,
            ]
        );

        return true;
    }

    public function subscribeToFeed(FacebookPage $page)
    {
        $output = $this->sendPostRequest(
            sprintf('/%s/tabs', $page->graph_id),
            [
                'app_id'       => $page->app->app_id,
                'access_token' => $page->page_token,
            ]
        );

        if ($output['success']) {
            $params = [
                'object'       => 'page',
                'fields'       => 'feed',
                'callback_url' => App::getRouter()->generate(
                        'api_channel_facebook_incoming', [], UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                'verify_token' => $page->verify_token,
                'access_token' => $this->app_token,
            ];
            $output = $this->sendPostRequest(
                sprintf('/%s/subscriptions', $page->app->app_id),
                $params
            );
        }

        return true;
    }

    private function sendGetRequest($uri, array $params)
    {
        // TODO: wrap in try/catch ?
        $fb      = $this->getClient();
        $request = $fb->get($uri);

        foreach ($params as $key => $val) {
            $request->getQuery()->set($key, $val);
        }

        $res = $request->send();

        $output = [];
        if ('text/javascript; charset=UTF-8' == $res->getContentType()) {
            $output = $res->json();
        } else {
            $body = $res->getBody();
            parse_str($body, $output);
        }

        return $output;
    }

    private function sendPostRequest($uri, array $params)
    {
        // TODO: wrap in try/catch ?
        $fb      = $this->getClient();
        $request = $fb->post($uri, [], $params);

        $res = $request->send();

        $output = [];
        if ('text/javascript; charset=UTF-8' == $res->getContentType()) {
            $output = $res->json();
        } else {
            $body = $res->getBody();
            parse_str($body, $output);
        }

        return $output;
    }
}
