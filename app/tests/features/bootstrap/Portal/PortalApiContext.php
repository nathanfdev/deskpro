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
namespace DpBehat\Portal;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Session;
use DpBehat\BaseContext;

/**
 * Class PortalApiContext.
 */
class PortalApiContext extends BaseContext
{
    /**
     * @Given I have guest portal api session with code :code
     *
     * @param string $code
     */
    public function iHaveGuestPortalApiSessionCode($code)
    {
        $session = new Session();
        $session->setAuth($code);

        $this->em()->persist($session);
        $this->em()->flush();
    }

    /**
     * @Given I have authorized portal api session with code :code for :email
     *
     * @param string $code
     * @param string $email
     */
    public function iHaveAuthorizedPortalApiSessionCode($code, $email)
    {
        /** @var \Application\DeskPRO\EntityRepository\Person $person_repository */
        $person_repository = $this->em()->getRepository('DeskPRO:Person');

        $person = $person_repository->findOneByEmail($email);
        if (!$person) {
            throw new \RuntimeException(sprintf('Person with email `%s` not found', $email));
        }

        $session = new Session();
        $session->setAuth($code);
        $session->setPerson($person);

        $this->em()->persist($session);
        $this->em()->flush();
    }

    /**
     * @Then I set chat email validation code :code for chat :chat_id
     *
     * @param int    $chat_id
     * @param string $code
     */
    public function iSetChatEmailValidationCode($chat_id, $code)
    {
        /** @var ChatConversation $conversation */
        $conversation = $this->em()->getRepository('DeskPRO:ChatConversation')->find($chat_id);
        if (!$conversation) {
            throw new \RuntimeException(sprintf('Conversation with id `%s` not found', $chat_id));
        }

        $conversation->setEmailValidationCode($code);

        $this->em()->persist($conversation);
        $this->em()->flush();
    }
}
