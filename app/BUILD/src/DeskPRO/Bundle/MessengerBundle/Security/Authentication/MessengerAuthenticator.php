<?php

namespace DeskPRO\Bundle\MessengerBundle\Security\Authentication;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\AppSecret\AppSecret;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class MessengerAuthenticator implements SimplePreAuthenticatorInterface
{
    const HTTP_REALM          = 'realm="x-deskpro-visitorid DeskPRO User Messenger API"';
    const VISITOR_HEADER_NAME = 'X-Deskpro-VisitorID';

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
        if (!$request->headers->has(self::VISITOR_HEADER_NAME)) {
            throw new UnauthorizedHttpException(self::HTTP_REALM, 'Visitor ID header is not set. Can\'t auth');
        }

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
