<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OIDC extends AbstractAdapter implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return [
            'name'            => isset($info['name']) ? $info['name'] : '',
            'first_name'      => isset($info['first_name']) ? $info['first_name'] : '',
            'last_name'       => isset($info['last_name']) ? $info['last_name'] : '',
            'email'           => isset($info['email']) ? $info['email'] : '',
            'email_confirmed' => true,
        ];
    }

    /**
     * @return \Orb\Auth\Adapter\OIDC
     */
    protected function _createAuthAdapterObject()
    {
        $options = $this->usersource->options;

        return new \Orb\Auth\Adapter\OIDC($options);
    }

    public function getAgentLogoutRedirectUrl()
    {
        return '';
    }

    public function getUserLogoutRedirectUrl()
    {
        return '';
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            UsersourceInfo::CAPABILITY_SSO,
            UsersourceInfo::CAPABILITY_SSO_JS,
            UsersourceInfo::CAPABILITY_LOGIN_TEXT_BTN,
        ];
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
