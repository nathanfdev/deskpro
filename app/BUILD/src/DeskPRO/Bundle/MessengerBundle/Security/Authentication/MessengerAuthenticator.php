<?php

namespace DeskPRO\Bundle\MessengerBundle\Security\Authentication;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\AppSecret\AppSecret;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class MessengerAuthenticator implements SimplePreAuthenticatorInterface
{
    const HTTP_REALM      = 'realm="DeskPRO User Messenger API"';
    const APP_HEADER_NAME = 'X-DeskPRO-App-ID';

    /**
     * @var string
     */
    private $secret;

    public function __construct(AppSecret $appSecret)
    {
        $this->secret = $appSecret->getAppSecret();
    }

    public function createToken(Request $request, $providerKey)
    {
        return new AnonymousToken($this->secret, new Person(), ['ROLE_API']);
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof AnonymousToken;
    }
}
