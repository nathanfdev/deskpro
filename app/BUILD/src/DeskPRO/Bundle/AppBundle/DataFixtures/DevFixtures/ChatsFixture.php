<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ChatsFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    const NUM_CHAT_CONVERSATIONS = 20;

    /** @var \Application\DeskPRO\Entity\Person $person */
    private $agent;

    /** @var \Application\DeskPRO\Entity\Person $person */
    private $person;

    /** @var ChatConversation $conversation */
    private $conversation;

    /** @var \DateTime $startDate */
    private $startDate;

    private $subjects = [
        'Hi there, how can I help you? | Hi | Do you have a problem? | File: Screen Shot 2015-11-27 at 11.02.12 AM.png (493.95 KB) | Yes look at this file | ok ill have a look',
        'hey would ya help me? | I need some help here | Sure, what seems to be the problem? | I cant figure this out at all.... | Well let me help you with that!',
        'oh, well hello there | this is admin can I help you | yes, help me | you should see my custom data',
    ];

    private $departments = [
        'chat_department.support',
        'chat_department.sales',
    ];

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 90;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->agent   = $this->getReference('admin');
        $people        = $this->fetchIds(self::TABLE_PEOPLE);
        $this->person  = $this->manager->getRepository('DeskPRO:Person')->find($this->faker->randomElement($people));
        $this->loadCustomDefChat();
        $this->loadChatConversations();
        $this->loadChatMessages();
    }

    private function loadCustomDefChat()
    {
        $sql = <<<SQL
            INSERT INTO `custom_def_chat` (`parent_id`, `app_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `default_value`, `is_agent_field`)
            VALUES
              (NULL, NULL, '', 0, 0, 'Chat text', 'this is a text box for a chat', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Text', X'613A303A7B7D', 1, 1, 0, NULL, 0),
              (NULL, NULL, '', 0, 0, 'chat toggle it\'', 'this is a toggle for chat', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Toggle', X'613A303A7B7D', 1, 1, 0, '', 0);
SQL;

        $this->db->exec($sql);
    }

    private function loadChatConversations()
    {
        $status        = ChatConversation::STATUS_ENDED;
        $conversations = [];
        $i             = 0;
        while ($i++ < self::NUM_CHAT_CONVERSATIONS) {
            $cc = new ChatConversation();
            $cc->setPerson($this->person)
                ->setStatus($status)
                ->setPersonName($this->person->getDisplayName())
                ->setPersonEmail($this->person->getEmail() ?: '')
                ->setEndedBy(ChatConversation::ENDED_AGENT)
                ->setAgent($this->agent)
                ->setDateCreated($this->faker->dateTimeBetween('-1 month', '-6 hours'))
                ->setSubject($this->faker->randomElement($this->subjects))
                ->setDepartment($this->getReference($this->faker->randomElement($this->departments)));
            $conversations[] = $cc;
        }

        foreach ($conversations as $conversation) {
            $this->manager->persist($conversation);
        }
        $this->manager->flush();
    }

    private function loadChatMessages()
    {
        $conversations = $this->manager->getRepository('DeskPRO:ChatConversation')->findAll();
        /** @var ChatConversation $conversation */
        foreach ($conversations as $conversation) {
            $this->conversation = $conversation;
            $this->startDate    = $conversation->date_created;
            $this->createMessageStarted();
            $this->createMessageNewUserTrack();
            $this->createMessageUserJoined();
            $this->createMessageAssigned();
            $this->createAgentMessage();
            $this->createUserMessage();
            $this->createMessageEnded();
            $this->manager->flush();
        }
    }

    private function createMessageStarted()
    {
        $content = ['phrase_id' => 'message_started'];
        $message = new ChatMessage();
        $message
            ->setContent(json_encode($content))
            ->setIsSys(true)
            ->setIsUserHidden(true)
            ->setIsHtml(true)
            ->setMetadata($content);
        $message->conversation = $this->conversation;
        $message->person_name  = $this->person->getDisplayName();
        $message->date_created = $this->startDate;
        $this->manager->persist($message);
    }

    private function createMessageNewUserTrack()
    {
        $content = [
            'phrase_id' => 'msg_new_user_track',
            'label'     => '<a href = "http://old-portal.dev:8080/" target = "_blank" title = "http://old-portal.dev:8080/">old-portal.dev:8080</a>',
        ];
        $message = new ChatMessage();
        $message
            ->setContent(json_encode($content))
            ->setIsSys(true)
            ->setIsUserHidden(true)
            ->setIsHtml(true)
            ->setMetadata($content);
        $message->conversation = $this->conversation;
        $message->person_name  = $this->person->getDisplayName();
        $message->date_created = $this->addInterval('PT1M');
        $this->manager->persist($message);
    }

    private function createMessageUserJoined()
    {
        $content = [
            'phrase_id' => 'message_user - joined',
            'name'      => $this->agent->getDisplayName(),
        ];
        $message = new ChatMessage();
        $message
            ->setContent(json_encode($content))
            ->setIsSys(true)
            ->setIsUserHidden(false)
            ->setIsHtml(false)
            ->setMetadata($content);
        $message->conversation = $this->conversation;
        $message->date_created = $this->addInterval('PT2M');
        $this->manager->persist($message);
    }

    private function createMessageAssigned()
    {
        $content = [
            'phrase_id' => 'message_assigned',
            'name'      => $this->agent->getDisplayName(),
        ];
        $message = new ChatMessage();
        $message
            ->setContent(json_encode($content))
            ->setIsSys(true)
            ->setIsUserHidden(false)
            ->setIsHtml(false)
            ->setMetadata($content);
        $message->conversation = $this->conversation;
        $message->person_name  = $this->person->getDisplayName();
        $message->date_created = $this->addInterval('PT3M');
        $this->manager->persist($message);
    }

    private function createAgentMessage()
    {
        $message = new ChatMessage();
        $message
            ->setAuthor($this->agent)
            ->setContent($this->faker->sentence())
            ->setIsSys(false)
            ->setIsUserHidden(false)
            ->setIsHtml(false)
            ->setMetadata(
                [
                    'person_avatar'      => 'http://www.gravatar.com/avatar/59235f35e4763abb0b547bd093562f6e?&s=40&d=mm',
                    'person_avatar_icon' => 'http://www.gravatar.com/avatar/59235f35e4763abb0b547bd093562f6e?&s=16&d=mm',
                ]
            )
            ->setOrigin(ChatMessage::ORIGIN_AGENT);
        $message->conversation = $this->conversation;
        $message->person_name  = $this->agent->getDisplayName();
        $message->date_created = $this->addInterval('PT4M');
        $this->manager->persist($message);
    }

    private function createUserMessage()
    {
        $message = new ChatMessage();
        $message
            ->setContent($this->faker->sentence())
            ->setIsSys(false)
            ->setIsUserHidden(false)
            ->setIsHtml(false)
            ->setMetadata(['is_user_message' => true])
            ->setOrigin(ChatMessage::ORIGIN_USER);
        $message->conversation = $this->conversation;
        $message->person_name  = $this->person->getDisplayName();
        $message->date_created = $this->addInterval('PT5M');
        $this->manager->persist($message);
    }

    private function createMessageEnded()
    {
        $content = [
            'phrase_id' => 'message_ended - by',
            'name'      => $this->agent->getDisplayName(),
        ];
        $message = new ChatMessage();
        $message
            ->setContent(json_encode($content))
            ->setIsSys(true)
            ->setIsUserHidden(false)
            ->setIsHtml(false)
            ->setMetadata($content)
            ->setOrigin(ChatMessage::ORIGIN_USER);
        $message->conversation = $this->conversation;
        $message->person_name  = $this->person->getDisplayName();
        $message->date_created = $this->addInterval('PT6M');
        $this->manager->persist($message);
    }

    private function addInterval($interval)
    {
        $newTime = new \DateTimeImmutable($this->startDate->format('Y-m-d H:i:s'));

        return $newTime->add(new \DateInterval($interval));
    }
}
