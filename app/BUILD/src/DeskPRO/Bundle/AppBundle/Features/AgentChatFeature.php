<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\EntityRepository\Setting as SettingRepository;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatParticipant;
use DeskPRO\Component\Util\RandUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AgentChatFeature.
 */
class AgentChatFeature extends AbstractFeature
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
    public function enable(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.default_entity_manager');
        /** @var SettingRepository $settingsRepository */
        $settingsRepository = $em->getRepository(Setting::class);
        $this->copyIM($container->get('doctrine.orm.default_entity_manager'));
        $key = sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $this->getId());
        $settingsRepository->updateSetting($key, true);

        $tmpData = $em->getRepository(TmpData::class)->findBy(['name' => $key]);
        foreach ($tmpData as $tmpDatum) {
            if ($tmpDatum->getType() === 'feature_enable') {
                $em->remove($tmpDatum);
            }
        }
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function disable(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.default_entity_manager');
        /** @var SettingRepository $settingsRepository */
        $settingsRepository = $em->getRepository(Setting::class);
        $connection         = $em->getConnection();
        $classes            = [
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
            $key = sprintf('%s.%s', BetaFeatureInterface::BETA_FEATURES_KEY, $this->getId());
            $settingsRepository->updateSetting($key, false);
            $tmpData = $em->getRepository(TmpData::class)->findBy(['name' => $key]);
            foreach ($tmpData as $tmpDatum) {
                if ($tmpDatum->getType() === 'feature_disable') {
                    $em->remove($tmpDatum);
                }
            }
            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
        }
    }

    private function copyIM(EntityManager $em)
    {
        $chats = $this->getChats($em);
        foreach ($chats as $chat) {
            $participants = $this->getParticipants($em, $chat);
            if (count($participants) !== 2) {
                continue;
            }
            $messages  = $this->getMessages($em, $chat);
            $newChatId = $this->copyChat($em, $chat, $participants);
            $this->copyMessages($em, $newChatId, $messages);
        }
    }

    private function getChats(EntityManager $em)
    {
        $sql = <<<'SQL'
SELECT `id`, `date_created` FROM `chat_conversations` WHERE `is_agent` = 1
SQL;

        return $em->getConnection()->fetchAll($sql);
    }

    private function getParticipants(EntityManager $em, $chat)
    {
        $sql = <<<'SQL'
SELECT `person_id` FROM `chat_conversation_to_person` WHERE `conversation_id` = ?
SQL;

        return $em->getConnection()->fetchAllCol($sql, [$chat['id']]);
    }

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

    private function copyChat(EntityManager $em, $chat, $participants)
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

            $participantsInsert = <<<'INSERT'
INSERT INTO `agent_chat_participant` (`agent_chat_id`, `person_id`) VALUES (:agent_chat_id, :person_id)
INSERT;
            $statement = $connection->prepare($participantsInsert);
            foreach ($participants as $participant) {
                $statement->execute(['agent_chat_id' => $newChatId, 'person_id' => $participant]);
            }
        }

        return $newChatId;
    }

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
   SET `date_last_message` = '{$message['date_created']}'
 WHERE `id` = {$newChatId}
UPDATE;

        $connection->executeQuery($update);
    }
}
