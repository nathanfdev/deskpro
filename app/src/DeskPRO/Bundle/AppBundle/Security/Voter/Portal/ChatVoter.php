<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;

class ChatVoter extends AbstractVoter
{
    const CHAT_VIEW = 'CHAT_VIEW';

    protected function getSupportedAttributes()
    {
        return array(self::CHAT_VIEW);
    }

    /**
     * @param string                                                                          $attribute
     * @param object                                                                          $chat
     * @param \Application\DeskPRO\Entity\Person|\Application\DeskPRO\People\PersonGuest|null $user
     *
     * @return bool
     */
    protected function isGranted($attribute, $chat, $user = null)
    {
        if (!$chat instanceof ChatConversation) {
            throw new InvalidArgumentException('expected ChatConversation entity, but got "'.get_class($chat).'"');
        }

        // none of the attributes currently supported by this voter will grant unauthenticated tokens
        if (!$this->isLoggedIn($user)) {
            return false;
        }

        $decision = false;

        switch ($attribute) {
            case static::CHAT_VIEW:
                $decision = $chat->isParticipating($user);
                break;
        }

        return $decision;
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return array('Application\\DeskPRO\\Entity\\ChatConversation');
    }
}
