<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use Doctrine\ORM\EntityManager;

/**
 * Class VoicePhoneCallVoter.
 */
class VoicePhoneCallVoter extends AbstractTicketsVoter
{
    const DELETE_RECORDING = 'delete_recording';

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return VoicePhoneCall::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        if (!$this->canUseTickets($user)) {
            return false;
        }

        /** @var VoicePhoneCall $phoneCall */
        $phoneCall = $context->getParent();

        switch ($attribute) {
            case PermissionGroupVoter::VIEW_LIST:
            case PermissionGroupVoter::VIEW:
            case PermissionGroupVoter::CREATE:
            case PermissionGroupVoter::MODIFY:
            case PermissionGroupVoter::DELETE:
                // not implemented yet
                return false;
            case self::DELETE_RECORDING:
                $attribute = $this->em->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy(['phoneCall' => $phoneCall]);
                // If we can't identify ticket we can't check permission
                if (!$attribute) {
                    return false;
                }
                $ticket  = $attribute->getMessage()->getTicket();
                $checker = $this->getTicketChecker($user);

                return $checker->canModifyMessages($ticket, 'delete_voice_recordings')
                        || $checker->canModifyMessages($ticket, 'delete_voice_messages');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        // no access for now
        return false;
    }
}
