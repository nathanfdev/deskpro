<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Orb\Auth\DPOAuth2Proxy;
use Orb\Auth\Identity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DeskproOauth2Proxy extends AbstractAdapter implements ContainerAwareInterface
{
    /** @var DeskproContainer */
    private $container;

    public function getFieldsFromIdentity(Identity $identity)
    {
        $info = $identity->getRawData();

        return [
            'email'           => $info['email'],
            'email_confirmed' => true,
        ];
    }

    /**
     * @return \Orb\Auth\Adapter\DeskproOAuth2Proxy
     */
    protected function _createAuthAdapterObject()
    {
        $client = DPOAuth2Proxy::fromContainer($this->container);
        return new \Orb\Auth\Adapter\DeskproOAuth2Proxy($client);
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            UsersourceInfo::CAPABILITY_SOCIAL_LOGIN,
        ];
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

    public function setContainer( ContainerInterface $container = null )
    {
        if ($container instanceof DeskproContainer || is_null($container)) {
            $this->container = $container;
        }
    }
}
