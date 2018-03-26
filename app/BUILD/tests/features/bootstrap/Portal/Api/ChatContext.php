<?php

namespace DpBehat\Portal\Api;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;

/**
 * Class ChatContext.
 */
class ChatContext extends BaseContext
{
    /**
     * @Given I reset chat user info for chat :chatId
     *
     * @param int $chatId
     */
    public function iResetChatUserInfo($chatId)
    {
        $chatId = DataContext::replace($chatId);

        $conversation = $this->findConversation($chatId);
        $conversation->setPerson(null);

        $this->em()->persist($conversation);
        $this->em()->flush();
    }

    /**
     * @Given I set chat user :email for chat :chat_id
     *
     * @param int    $chatId
     * @param string $email
     */
    public function iSetChatUser($chatId, $email)
    {
        $chatId       = DataContext::replace($chatId);
        $conversation = $this->findConversation($chatId);

        try {
            $conversation->setPerson($this->findPerson($email));
        } catch (\RuntimeException $e) {
            $conversation->setPersonEmail($email);
        }

        $this->em()->persist($conversation);
        $this->em()->flush();
    }

    /**
     * @Given I reset chat should send transcript for chat :chatId
     *
     * @param int $chatId
     */
    public function iResetChatShouldSendTranscript($chatId)
    {
        $chatId = DataContext::replace($chatId);

        $conversation = $this->findConversation($chatId);
        $conversation->setShouldSendTranscript(false);

        $this->em()->persist($conversation);
        $this->em()->flush();
    }

    /**
     * @Then chat property :property should be equal to :expected for chat :chatId
     *
     * @param string $property
     * @param int    $expected
     * @param int    $chatId
     *
     * @throws \Exception
     */
    public function chatPropertyShouldBeEqual($property, $expected, $chatId)
    {
        $chatId       = DataContext::replace($chatId);
        $conversation = $this->findConversation($chatId);
        $actual       = $conversation->$property;

        if ($actual != $expected) {
            throw new \Exception(
                sprintf("The chat property value is '%s'", json_encode($actual))
            );
        }
    }

    /**
     * @Then chat property :property should be null for chat :chatId
     *
     * @param string $property
     * @param int    $chatId
     */
    public function chatPropertyShouldBeNull($property, $chatId)
    {
        $chatId       = DataContext::replace($chatId);
        $conversation = $this->findConversation($chatId);

        expect($conversation->$property)->toBe(null);
    }

    /**
     * @Then chat property :property should not be null for chat :chatId
     *
     * @param string $property
     * @param int    $chatId
     */
    public function chatPropertyShouldNotBeNull($property, $chatId)
    {
        $chatId       = DataContext::replace($chatId);
        $conversation = $this->findConversation($chatId);

        expect($conversation->$property)->notToBe(null);
    }

    /**
     * @Then I remember last :chatId chat message id
     *
     * @param int $chatId
     */
    public function iSaveConversationLastMessageId($chatId)
    {
        $chatId       = DataContext::replace($chatId);
        $conversation = $this->findConversation($chatId);

        $lastMessage = $conversation->messages->last();
        if ($lastMessage) {
            DataContext::setPlaceholder('lastCreatedId', $lastMessage->getId());
        }
    }

    /**
     * @param int $chatId
     *
     * @return ChatConversation
     */
    protected function findConversation($chatId)
    {
        $conversation = $this->repository(ChatConversation::class)->find($chatId);
        if (!$conversation) {
            throw new \RuntimeException(sprintf('Conversation with id `%s` not found', $chatId));
        }

        return $conversation;
    }

    /**
     * @param string $email
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    protected function findPerson($email)
    {
        /** @var \Application\DeskPRO\EntityRepository\Person $repository */
        $repository = $this->repository(Person::class);
        $person     = $repository->findOneByEmail($email);

        if (!$person) {
            throw new \RuntimeException(sprintf('Person with email `%s` not found', $email));
        }

        return $person;
    }
}
