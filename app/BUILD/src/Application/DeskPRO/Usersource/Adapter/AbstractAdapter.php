<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\Entity\Usersource;
use Orb\Auth\Adapter\PluginAdapter;
use Orb\Auth\Identity;
use Orb\Util\CapabilityInformerInterface;
use Orb\Util\Util;

abstract class AbstractAdapter implements CapabilityInformerInterface, IdentityFinderInterface
{
    /**
     * @var \Application\DeskPRO\Entity\Usersource
     */
    protected $usersource;

    /**
     * @var \Orb\Auth\Adapter\AdapterInterface
     */
    protected $_auth_adapter;

    public function __construct(Usersource $usersource)
    {
        $this->usersource = $usersource;
        $this->init();
    }

    protected function init()
    {
    }

    /**
     * Find a user identity just by an email address.
     *
     * @param string $input
     *
     * @return \Orb\Auth\Identity|null
     */
    public function findIdentityByInput($input)
    {
        return;
    }

    /**
     * Given an identity returned from an auth adapter, get the mapped fields that we can apply
     * to a Person record. For example, email addresses or names.
     *
     * @param \Orb\Auth\Identity $identity
     *
     * @return array
     */
    public function getFieldsFromIdentity(Identity $identity)
    {
        return [];
    }

    /**
     * @param array $info
     *
     * @return string
     */
    public function getDisplayName(array $info)
    {
        $order = ['display_name', 'username', 'name', 'email'];
        foreach ($order as $k) {
            if (!empty($info[$k])) {
                return $info[$k];
            }
        }

        return '';
    }

    /**
     * @param array $info
     *
     * @return string
     */
    public function getDisplayLink(array $info)
    {
        return '';
    }

    /**
     * Get the adapter.
     *
     * @return \Orb\Auth\Adapter\AdapterInterface|\Orb\Auth\Adapter\PluginAdapter
     */
    public function getAuthAdapter()
    {
        if ($this->_auth_adapter !== null) {
            return $this->_auth_adapter;
        }

        $this->_auth_adapter = $this->_createAuthAdapterObject();
        if ($this->_auth_adapter instanceof PluginAdapter) {
            if ($filter = $this->usersource->getOption('raw_info_filter')) {
                $this->_auth_adapter->setFilterExpression($filter);
            }
        }

        return $this->_auth_adapter;
    }

    public function applyResultToUser()
    {
    }

    /**
     * If the getAuthAdapter method returns an SsoCapableInterface, we need to implement this.
     *
     * @return string url
     */
    public function getAgentLogoutRedirectUrl()
    {
    }

    /**
     * If the getAuthAdapter method returns an SsoCapableInterface, we need to implement this.
     *
     * @return string url
     */
    public function getUserLogoutRedirectUrl()
    {
    }

    /**
     * Create a new instance of the adapter interface, using the usersource info
     * for options etc.
     *
     * @return \Orb\Auth\Adapter\AdapterInterface
     */
    abstract protected function _createAuthAdapterObject();

    public function getTypename()
    {
        return strtolower(Util::getBaseClassname($this));
    }

    /**
     * @param mixed $capability
     *
     * @return bool
     */
    public function isCapable($capability)
    {
        return in_array($capability, $this->getCapabilities());
    }

    public function getCodeName()
    {
        return implode('', array_slice(explode('\\', get_called_class()), -1));
    }
}
