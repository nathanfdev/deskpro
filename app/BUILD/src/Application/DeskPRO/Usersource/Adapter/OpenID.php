<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

//        $options['identity'] = 'https://me.yahoo.com/a/M8jHCYoVkoUib54qM0af7RjXhxVcQ2gOfWYkZSjJN0e1Epii1wKXq5TEuOmo';

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
