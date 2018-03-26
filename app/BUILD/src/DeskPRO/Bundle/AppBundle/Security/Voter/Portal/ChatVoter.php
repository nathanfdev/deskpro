<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ChatVoter extends AbstractVoter
{
    const CHAT_VIEW = 'CHAT_VIEW';

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $chat, TokenInterface $token)
    {
        if (!$chat instanceof ChatConversation) {
            throw new InvalidArgumentException('expected ChatConversation entity, but got "'.get_class($chat).'"');
        }

        $user = $token->getUser();

        // none of the attributes currently supported by this voter will grant unauthenticated tokens
        if (!$this->isLoggedIn($user)) {
            return false;
        }

        $decision = false;

        switch ($attribute) {
            case static::CHAT_VIEW:
                $decision = $chat->isParticipating($user) || $chat->isPersonOrganizationManager($user);
                break;
        }

        return $decision;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof ChatConversation && in_array($attribute, [
            self::CHAT_VIEW,
        ]);
    }
}
