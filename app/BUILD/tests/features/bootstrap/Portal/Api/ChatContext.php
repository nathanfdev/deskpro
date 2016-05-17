<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DpBehat\Portal\Api;

use Application\DeskPRO\Entity\ChatConversation;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DpBehat\BaseContext;

/**
 * Class ChatContext.
 */
class ChatContext extends BaseContext
{
    /**
     * @var int|null
     */
    public static $chatId;

    /**
     * @var AuthContext
     */
    protected $auth_context;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment        = $scope->getEnvironment();
        $this->auth_context = $environment->getContext('DpBehat\Portal\Api\AuthContext');
    }

    /**
     * @Given I create a chat and reference its' ID as chatId
     */
    public function iCreateAChatAndReferenceItsId()
    {
        $chat = new ChatConversation();

        $this->em()->persist($chat);
        $this->em()->flush();

        self::$chatId = $chat->getId();
    }

    /**
     * @Given I set chat email validation code :code for chat :chat_id
     *
     * @param int    $chat_id
     * @param string $code
     */
    public function iSetChatEmailValidationCode($chat_id, $code)
    {
        $conversation = $this->findConversation($chat_id);
        $conversation->setEmailValidationCode($code);

        $this->em()->persist($conversation);
        $this->em()->flush();
    }

    /**
     * @Given I reset chat user info for chat :chat_id
     *
     * @param int $chat_id
     */
    public function iResetChatUserInfo($chat_id)
    {
        $conversation = $this->findConversation($chat_id);
        $conversation->setPerson(null);

        $this->em()->persist($conversation);
        $this->em()->flush();
    }

    /**
     * @Given I set chat user :email for chat :chat_id
     *
     * @param int    $chat_id
     * @param string $email
     */
    public function iSetChatUser($chat_id, $email)
    {
        $conversation = $this->findConversation($chat_id);

        try {
            $conversation->setPerson($this->auth_context->findPerson($email));
        } catch (\RuntimeException $e) {
            $conversation->setPersonEmail($email);
        }

        $this->em()->persist($conversation);
        $this->em()->flush();
    }

    /**
     * @Given I reset chat should send transcript for chat :chat_id
     *
     * @param int $chat_id
     */
    public function iResetChatShouldSendTranscript($chat_id)
    {
        $conversation = $this->findConversation($chat_id);
        $conversation->setShouldSendTranscript(false);

        $this->em()->persist($conversation);
        $this->em()->flush();
    }

    /**
     * @Then chat property :property should be equal to :expected for chat :chat_id
     *
     * @param string $property
     * @param int    $expected
     * @param int    $chat_id
     *
     * @throws \Exception
     */
    public function chatPropertyShouldBeEqual($property, $expected, $chat_id)
    {
        $conversation = $this->findConversation($chat_id);
        $actual       = $conversation->$property;

        if ($actual != $expected) {
            throw new \Exception(
                sprintf("The chat property value is '%s'", json_encode($actual))
            );
        }
    }

    /**
     * @Then chat property :property should be null for chat :chat_id
     *
     * @param string $property
     * @param int    $chat_id
     */
    public function chatPropertyShouldBeNull($property, $chat_id)
    {
        $conversation = $this->findConversation($chat_id);
        expect($conversation->$property)->toBe(null);
    }

    /**
     * @Then chat property :property should not be null for chat :chat_id
     *
     * @param string $property
     * @param int    $chat_id
     */
    public function chatPropertyShouldNotBeNull($property, $chat_id)
    {
        $conversation = $this->findConversation($chat_id);
        expect($conversation->$property)->notToBe(null);
    }

    /**
     * @param int $chat_id
     *
     * @return ChatConversation
     */
    protected function findConversation($chat_id)
    {
        /** @var ChatConversation $conversation */
        $conversation = $this->em()->getRepository('DeskPRO:ChatConversation')->find($chat_id);
        if (!$conversation) {
            throw new \RuntimeException(sprintf('Conversation with id `%s` not found', $chat_id));
        }

        return $conversation;
    }
}
