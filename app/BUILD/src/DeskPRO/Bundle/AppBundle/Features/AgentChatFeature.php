<?php

namespace DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatParticipant;
use DeskPRO\Bundle\AppBundle\Entity\PersonOnboarding;
use DeskPRO\Component\Util\RandUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AgentChatFeature.
 */
class AgentChatFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'agent_chat';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Agent IM v2';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved agent instant messaging.';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return <<<'HTML'
Agent IM v2 replaces the current agent instant messaging feature with a new and improved version that is more powerful
 and easier to use.<br/><br/>
Installing Agent IM v2 beta will copy your old conversations over to the new system and then disable the old 
messaging system. If you later decide you wish to go back to the old system, you can disable IM v2.

HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disabling IM v2 will revert your helpdesk back to using the previous instant messaging system.<br/><br/>
Please note that any new conversations you have started or contunued on the IM v2 system will <strong>NOT</strong> be
 copied back to the old system. New conversations will be <strong>lost</strong> forever.
HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailability()
    {
        return [self::AVAILABLE_EVERYWHERE];
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabledOnInstall()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function needAgentReload()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeEnable(ContainerInterface $container)
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.orm.default_entity_manager');
        $this->copyIM($em);
        $this->createOnboardings($em);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDisable(ContainerInterface $container)
    {
        $em         = $container->get('doctrine.orm.default_entity_manager');
        $connection = $em->getConnection();
        $classes    = [
            AgentChat::class,
            AgentChatMessage::class,
            AgentChatParticipant::class,
        ];

        $parts = [];
        $em->beginTransaction();
        try {
            $em->getRepository(AgentChat::class)->createQueryBuilder('ac')->delete()->getQuery()->execute();

            foreach ($classes as $class) {
                $meta    = $em->getClassMetadata($class);
                $parts[] = "ALTER TABLE {$meta->getTableName()} AUTO_INCREMENT = 1";
            }

            $connection->query(implode(';', $parts));
            $em->commit();
            $this->createOnboardings($em, true);
        } catch (\Exception $e) {
            $em->rollback();
        }
    }

    /**
     * @param EntityManager $em
     */
    private function copyIM(EntityManager $em)
    {
        $chats    = $this->getChats($em);
        $setAdmin = false;
        foreach ($chats as $chat) {
            $participants = $this->getParticipants($em, $chat);
            $messages     = $this->getMessages($em, $chat);

            if (count($participants) == 2) {
                $newChatId = $this->find121Chat($em, $chat, $participants);
            } else {
                $newChatId = $this->findGroupChat($em, $chat, $participants);
                $setAdmin  = true;
            }

            $this->copyMessages($em, $newChatId, $messages);
            if ($setAdmin) {
                $this->setAdmin($em, $newChatId);
            }
        }
    }

    /**
     * @param EntityManager $em
     *
     * @return array
     */
    private function getChats(EntityManager $em)
    {
        $sql = <<<'SQL'
SELECT `id`, `date_created` FROM `chat_conversations` WHERE `is_agent` = 1
SQL;

        return $em->getConnection()->fetchAll($sql);
    }

    private function setAdmin(EntityManager $em, $chatId)
    {
        /** @var \Application\DeskPRO\DBAL\Connection $connection */
        $connection = $em->getConnection();
        $sql        = <<<'SQL'
SELECT `person_id` FROM `agent_chat_message` WHERE `agent_chat_id` = ? GROUP BY `person_id` ORDER BY `date_created` 
SQL;
        $personIds = $connection->fetchAllCol($sql, [$chatId]);
        $person    = null;
        foreach ($personIds as $personId) {
            if (!$personId) {
                continue;
            }
            if ($person = $em->find(Person::class, $personId)) { // we need actual person to avoid constraint fail
                break;
            }
        }

        if (!$person) { // edge case fallback - all users in chat are deleted?
            $person = $em
                ->getRepository(Person::class)
                ->findOneBy(['can_admin' => true, 'is_deleted' => false, 'is_disabled' => false]);
            $this->insertParticipants($em, $chatId, [$person->getId()]);
        }

        $sql = <<<'SQL'
        UPDATE `agent_chat` SET `admin_id` = ? WHERE `id` = ?
SQL;
        $connection->executeQuery($sql, [$person->getId(), $chatId]);
    }

    /**
     * @param EntityManager $em
     * @param array         $chat
     *
     * @return mixed
     */
    private function getParticipants(EntityManager $em, $chat)
    {
        $sql = <<<'SQL'
SELECT `person_id` FROM `chat_conversation_to_person` WHERE `conversation_id` = ?
SQL;

        return $em->getConnection()->fetchAllCol($sql, [$chat['id']]);
    }

    /**
     * @param EntityManager $em
     * @param array         $chat
     *
     * @return array
     */
    private function getMessages(EntityManager $em, $chat)
    {
        $sql = <<<'SQL'
SELECT `author_id`, `person_name`, `content`, `date_created`, `date_received` 
  FROM `chat_messages`
 WHERE `conversation_id` = ?
 ORDER BY `date_created` ASC
SQL;

        return $em->getConnection()->fetchAll($sql, [$chat['id']]);
    }

    /**
     * @param EntityManager $em
     * @param array         $chat
     * @param array         $participants
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return mixed|string
     */
    private function find121Chat(EntityManager $em, $chat, $participants)
    {
        $connection = $em->getConnection();
        // try to find same chat before create new one
        $find = <<<'FIND'
SELECT `ac`.`id` FROM `agent_chat` AS `ac`
INNER JOIN `agent_chat_participant` AS `acp` ON `ac`.`id` = `acp`.`agent_chat_id`
INNER JOIN `agent_chat_participant` AS `acp2` ON `ac`.`id` = `acp2`.`agent_chat_id`
WHERE `ac`.`type` = 'agent' AND `acp`.`person_id` = ? AND `acp2`.`person_id` = ?
FIND;

        if (!$newChatId = $connection->fetchColumn($find, [$participants[0], $participants[1]])) {
            $connection->insert('agent_chat', ['type' => 'agent', 'date_created' => $chat['date_created']]);
            $newChatId = $connection->lastInsertId();

            $this->insertParticipants($em, $newChatId, $participants);
        }

        return $newChatId;
    }

    /**
     * @param EntityManager $em
     * @param array         $chat
     * @param array         $participants
     *
     * @return string
     */
    private function findGroupChat(EntityManager $em, $chat, $participants)
    {
        $connection = $em->getConnection();
        if (!$newChat = $em->getRepository(AgentChat::class)->findGroupChat($participants)) {
            $people    = $em->getRepository(Person::class)->findBy(['id' => $participants], null, 4);
            $nameParts = [];
            foreach ($people as $person) {
                $nameParts[] = $person->getFirstName() ?: $person->getName();
            }
            $connection->insert('agent_chat', [
                    'type'         => 'group',
                    'date_created' => $chat['date_created'],
                    'name'         => implode(', ', $nameParts).' Group',
                ]
            );
            $newChatId = $connection->lastInsertId();
            $this->insertParticipants($em, $newChatId, $participants);
        } else {
            $newChatId = $newChat->getId();
        }

        return $newChatId;
    }

    private function insertParticipants(EntityManager $em, $newChatId, $participants)
    {
        $connection         = $em->getConnection();
        $participantsInsert = <<<'INSERT'
INSERT INTO `agent_chat_participant` (`agent_chat_id`, `person_id`) VALUES (:agent_chat_id, :person_id)
INSERT;
        $statement = $connection->prepare($participantsInsert);
        foreach ($participants as $participant) {
            $statement->execute(['agent_chat_id' => $newChatId, 'person_id' => $participant]);
        }
    }

    /**
     * @param EntityManager $em
     * @param int           $newChatId
     * @param array         $messages
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function copyMessages(EntityManager $em, $newChatId, $messages)
    {
        if (!$messages) {
            return;
        } // no messages
        $connection  = $em->getConnection();
        $newMessages = [];
        foreach ($messages as $message) {
            $data = [
                'agent_chat_id' => $newChatId,
                'person_id'     => $message['author_id'],
                'uuid'          => RandUtils::uuidV4(),
                'person_name'   => $message['person_name'],
                'message'       => '<p>'.$message['content'].'</p>',
                'date_created'  => $message['date_created'],
                'status'        => 2,
                'metadata'      => '[]',
            ];
            $newMessages[] = $data;
        }
        $connection->batchInsert('agent_chat_message', $newMessages);
        // note here we are explicitly using that $message var contains last from $messages
        $update = <<<UPDATE
UPDATE `agent_chat` 
   SET `date_last_message` = IF(`date_last_message` > '{$message['date_created']}', `date_last_message`, '{$message['date_created']}')
 WHERE `id` = {$newChatId}
UPDATE;

        $connection->executeQuery($update);
    }

    private function createOnboardings(EntityManager $em, $finished = false)
    {
        $agents = $em->getRepository(Person::class)->findBy([
            'is_agent'    => true,
            'is_disabled' => false,
            'is_deleted'  => false,
        ]);

        foreach ($agents as $agent) {
            /** @var Person $agent */
            $onboarding = new PersonOnboarding();
            $onboarding
                ->setApplication(PersonOnboarding::APPLICATION_AGENT)
                ->setOnboardingClass('newIm');
            if ($finished) {
                //add finished onboardings for agents were added after feature was enabled, so next time they
                // wouldn't see this onboarding again
                $onboarding->setStatus(PersonOnboarding::STATUS_COMPLETED);
            }

            $agent->addOnboarding($onboarding);
            $em->persist($agent);
        }
        $em->flush();
    }
}
