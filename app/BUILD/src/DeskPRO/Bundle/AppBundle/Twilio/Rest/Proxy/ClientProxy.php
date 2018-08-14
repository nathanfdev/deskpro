<?php

namespace DeskPRO\Bundle\AppBundle\Twilio\Rest\Proxy;

use Twilio\Rest\Client;

/**
 * Class ClientProxy.
 */
class ClientProxy extends Client
{
    /**
     * @var string
     */
    protected $apiProxyUrl;

    /**
     * @var string
     */
    protected $taskRouterProxyUrl;

    /**
     * @var string
     */
    protected $accountsProxyUrl;

    /**
     * @var string
     */
    protected $proxyPricingUrl;

    /**
     * @var string
     */
    protected $proxyUsername;

    /**
     * @var string
     */
    protected $proxyPassword;

    /**
     * @return string
     */
    public function getProxyUsername()
    {
        return $this->proxyUsername;
    }

    /**
     * @param string $proxyUsername
     *
     * @return $this
     */
    public function setProxyUsername($proxyUsername)
    {
        $this->proxyUsername = $proxyUsername;

        return $this;
    }

    /**
     * @return string
     */
    public function getProxyPassword()
    {
        return $this->proxyPassword;
    }

    /**
     * @param string $proxyPassword
     *
     * @return $this
     */
    public function setProxyPassword($proxyPassword)
    {
        $this->proxyPassword = $proxyPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiProxyUrl()
    {
        return $this->apiProxyUrl;
    }

    /**
     * @param string $apiProxyUrl
     *
     * @return $this
     */
    public function setApiProxyUrl($apiProxyUrl)
    {
        $this->apiProxyUrl = $apiProxyUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getTaskRouterProxyUrl()
    {
        return $this->taskRouterProxyUrl;
    }

    /**
     * @param string $taskRouterProxyUrl
     *
     * @return $this
     */
    public function setTaskRouterProxyUrl($taskRouterProxyUrl)
    {
        $this->taskRouterProxyUrl = $taskRouterProxyUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountsProxyUrl()
    {
        return $this->accountsProxyUrl;
    }

    /**
     * @param string $accountsProxyUrl
     *
     * @return $this
     */
    public function setAccountsProxyUrl($accountsProxyUrl)
    {
        $this->accountsProxyUrl = $accountsProxyUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getProxyPricingUrl()
    {
        return $this->proxyPricingUrl;
    }

    /**
     * @param string $proxyPricingUrl
     *
     * @return $this
     */
    public function setProxyPricingUrl($proxyPricingUrl)
    {
        $this->proxyPricingUrl = $proxyPricingUrl;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getApi()
    {
        if (!$this->_api) {
            $this->_api = new ApiProxy($this);
        }

        return $this->_api;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskrouter()
    {
        if (!$this->_taskrouter) {
            $this->_taskrouter = new TaskRouterProxy($this);
        }

        return $this->_taskrouter;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAccounts()
    {
        if (!$this->_accounts) {
            $this->_accounts = new AccountsProxy($this);
        }

        return $this->_accounts;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPricing()
    {
        if (!$this->_pricing) {
            $this->_pricing = new PricingProxy($this);
        }

        return $this->_pricing;
    }

    /**
     * {@inheritdoc}
     */
    public function request($method, $uri, $params = [], $data = [], $headers = [], $username = null, $password = null, $timeout = null)
    {
        if (!$username && $this->proxyUsername) {
            $username = $this->proxyUsername;
        }
        if (!$password && $this->proxyPassword) {
            $password = $this->proxyPassword;
        }

        return parent::request($method, $uri, $params, $data, $headers, $username, $password, $timeout);
    }
}
