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

/**
 * DeskPRO.
 */

namespace DpBehat\Portal\Api;

use Application\DeskPRO\Entity\ChatConversation;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;

/**
 * Class ChatContext.
 */
class ChatContext extends BaseContext
{
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
     * @Given I set chat email validation code :code for chat :chatId
     *
     * @param int    $chatId
     * @param string $code
     */
    public function iSetChatEmailValidationCode($chatId, $code)
    {
        $chatId = DataContext::replace($chatId);

        $conversation = $this->findConversation($chatId);
        $conversation->setEmailValidationCode($code);

        $this->em()->persist($conversation);
        $this->em()->flush();
    }

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
            $conversation->setPerson($this->auth_context->findPerson($email));
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
}
