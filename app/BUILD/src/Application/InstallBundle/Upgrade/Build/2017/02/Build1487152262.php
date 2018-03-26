<?php

namespace Application\InstallBundle\Upgrade\Build;

use DeskPRO\Component\Util\RandUtils;

class Build1487152262 extends AbstractBuild
{
    /**
     * @throws \Throwable
     */
    public function run()
    {
        if ($this->container->get('deskpro.feature_flags')->hasFeature('agent_chat')) {
            $this->out('Copying old IM messages');
            $this->copyIM();
        } else {
            $this->out('Skip IM messages copy. Experimental feature is not enabled');
        }
    }

    private function copyIM()
    {
        $chats = $this->getChats();
        foreach ($chats as $chat) {
            $participants = $this->getParticipants($chat);
            if (count($participants) !== 2) {
                continue;
            }
            $messages  = $this->getMessages($chat);
            $newChatId = $this->copyChat($chat, $participants);
            $this->copyMessages($newChatId, $messages);
        }
    }

    private function getChats()
    {
        $sql = <<<'SQL'
SELECT `id`, `date_created` FROM `chat_conversations` WHERE `is_agent` = 1
SQL;

        return $this->getDbConnection()->fetchAll($sql);
    }

    private function getParticipants($chat)
    {
        $sql = <<<'SQL'
SELECT `person_id` FROM `chat_conversation_to_person` WHERE `conversation_id` = ?
SQL;

        return $this->getDbConnection()->fetchAllCol($sql, [$chat['id']]);
    }

    private function getMessages($chat)
    {
        $sql = <<<'SQL'
SELECT `author_id`, `person_name`, `content`, `date_created`, `date_received` 
  FROM `chat_messages`
 WHERE `conversation_id` = ?
 ORDER BY `date_created` ASC
SQL;

        return $this->getDbConnection()->fetchAll($sql, [$chat['id']]);
    }

    private function copyChat($chat, $participants)
    {
        $connection = $this->getDbConnection();
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

    private function copyMessages($newChatId, $messages)
    {
        if (!$messages) {
            return;
        } // no messages
        $connection  = $this->getDbConnection();
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
   SET `date_last_message` = GREATEST(`date_last_message`, '{$message['date_created']}')
 WHERE `id` = {$newChatId}
UPDATE;

        $this->execDbQuery('default', $update);
    }
}
