<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\Identity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OpenID extends \Application\DeskPRO\Usersource\Adapter\AbstractAdapter implements ContainerAwareInterface
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
     * @return \Orb\Auth\Adapter\OpenId
     */
    protected function _createAuthAdapterObject()
    {
        $options                                         = $this->usersource->options;
        $realm                                           = $this->container->get('brand_stack')->getActive()->getSetting('core.deskpro_url');
        $options[\Orb\Auth\Adapter\OpenId::OPTION_REALM] = $realm;

        return new \Orb\Auth\Adapter\OpenId($options);
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
        $capabilities = [
            UsersourceInfo::CAPABILITY_SSO,
            UsersourceInfo::CAPABILITY_SSO_JS,
        ];

        if (isset($this->usersource->options['login_custom_text']) && $custom_button_text = $this->usersource->options['login_custom_text']) {
            $capabilities[] = UsersourceInfo::CAPABILITY_LOGIN_TEXT_BTN;
        }

        return $capabilities;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
