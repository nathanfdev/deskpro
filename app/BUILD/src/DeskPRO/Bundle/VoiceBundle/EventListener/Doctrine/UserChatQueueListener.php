<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\VoiceBundle\Settings\ChatSettingsResolver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserChatQueueListener.
 *
 * todo enable chat.use for targets
 */
class UserChatQueueListener
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ChatSettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param EntityManager        $em
     * @param ChatSettingsResolver $settingsResolver
     */
    public function __construct(EntityManager $em, ChatSettingsResolver $settingsResolver)
    {
        $this->em               = $em;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @ORM\PostPersist()
     *
     * @param UserChatQueue $chatQueue
     */
    public function setDefaultQueue(UserChatQueue $chatQueue)
    {
        $defaultQueueId = $this->settingsResolver->getDefaultQueue();
        if ($defaultQueueId) {
            $defaultQueue = $this->em->getRepository(UserChatQueue::class)->find($defaultQueueId);
        } else {
            $defaultQueue = null;
        }

        if (!$defaultQueue) {
            $this->getSettingsRepo()->updateSetting(ChatSettingsResolver::USER_CHAT_DEFAULT_QUEUE, $chatQueue->getId());
        }
    }

    /**
     * @ORM\PreRemove()
     *
     * @param UserChatQueue $chatQueue
     */
    public function unsetDefaultQueue(UserChatQueue $chatQueue)
    {
        if ($chatQueue->getId() === $this->settingsResolver->getDefaultQueue()) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('u')
                ->from(UserChatQueue::class, 'u')
                ->where('u.id != :id')
                ->setParameter('id', $chatQueue->getId())
                ->setMaxResults(1)
            ;

            /** @var UserChatQueue $newDefaultQueue */
            $newDefaultQueue = $qb->getQuery()->getOneOrNullResult();
            if ($newDefaultQueue) {
                $this->getSettingsRepo()->updateSetting(ChatSettingsResolver::USER_CHAT_DEFAULT_QUEUE, $newDefaultQueue->getId());
            } else {
                $this->getSettingsRepo()->updateSetting(ChatSettingsResolver::USER_CHAT_DEFAULT_QUEUE, null);
            }
        }
    }

    /**
     * @return \Doctrine\ORM\EntityRepository|\Application\DeskPRO\EntityRepository\Setting
     */
    protected function getSettingsRepo()
    {
        return $this->em->getRepository(Setting::class);
    }
}
