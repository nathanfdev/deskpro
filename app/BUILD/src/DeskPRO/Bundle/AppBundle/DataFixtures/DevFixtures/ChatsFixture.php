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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ChatsFixture extends DeskProAbstractFixture implements OrderedFixtureInterface
{
    const NUM_CHAT_CONVERSATIONS = 3;

    /** @var \Application\DeskPRO\Entity\Person $person */
    private $agent;

    /** @var \Application\DeskPRO\Entity\Person $person */
    private $person;

    /** @var ChatConversation $conversation */
    private $conversation;

    /** @var \DateTime $startDate */
    private $startDate;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 80;
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
                ->setPersonEmail($this->person->getEmail())
                ->setEndedBy(ChatConversation::ENDED_AGENT)
                ->setAgent($this->agent);
            $cc->date_created = $this->faker->dateTimeBetween('-2 months', '-10 days');
            $conversations[]  = $cc;
        }

        $conversations[0]->subject    = 'Hi there, how can I help you? | Hi | Do you have a problem? | File: Screen Shot 2015-11-27 at 11.02.12 AM.png (493.95 KB) | Yes look at this file | ok ill have a look';
        $conversations[0]->department = $this->getReference('chat_department.support');
        $conversations[1]->subject    = 'hey would ya help me? | I need some help here | Sure, what seems to be the problem? | I cant figure this out at all.... | Well let me help you with that!';
        $conversations[1]->department = $this->getReference('chat_department.sales');
        $conversations[2]->subject    = 'oh, well hello there | this is admin can I help you | yes, help me | you should see my custom data';
        $conversations[2]->department = $this->getReference('chat_department.sales');

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
        /*
                $sql = <<<SQL
                    INSERT INTO `chat_messages` (`id`, `conversation_id`, `author_id`, `tag`, `origin`, `person_name`, `content`, `is_sys`, `is_user_hidden`, `is_html`, `metadata`, `date_created`, `date_received`)
                    VALUES
                        (1, 1, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_started\"}', 1, 1, 1, X'613A313A7B733A393A227068726173655F6964223B733A31353A226D6573736167655F73746172746564223B7D', '2015-11-29 20:01:33', NULL),
                        (2, 1, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"msg_new_user_track\",\"label\":\" < a href = \\\"http://old-portal.dev:8080/\\\" target = \\\"_blank\\\" title = \\\"http://old-portal.dev:8080/\\\" > old - portal . dev:8080 /< / a>\"}', 1, 1, 1, X'613A333A7B733A31343A226E65775F757365725F747261636B223B733A32373A22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223B733A353A226C6162656C223B733A3131343A223C6120687265663D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F22207461726765743D225F626C616E6B22207469746C653D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223E6F6C642D706F7274616C2E6465763A383038302F3C2F613E223B733A393A227068726173655F6964223B733A31383A226D73675F6E65775F757365725F747261636B223B7D', '2015-11-29 20:01:33', NULL),
                        (3, 1, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:01:40', '2015-11-29 20:01:45'),
                        (4, 1, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_assigned\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A383A7B733A31333A22636861745F61737369676E6564223B623A313B733A31313A2261737369676E65645F746F223B693A313B733A31333A2261737369676E65645F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31353A2261737369676E65645F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B733A31353A226F6C645F61737369676E65645F746F223B693A303B733A31373A226F6C645F61737369676E65645F6E616D65223B733A303A22223B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31363A226D6573736167655F61737369676E6564223B7D', '2015-11-29 20:01:40', '2015-11-29 20:01:45'),
                        (5, 1, 1, NULL, 'agent', 'Admin Admin', '<div>Hi there, how can I help you?</div>', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:01:47', '2015-11-29 20:01:51'),
                        (6, 1, NULL, NULL, 'user', 'Joe', 'Hi', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:01:54', NULL),
                        (7, 1, 1, NULL, 'agent', 'Admin Admin', 'Do you have a problem?', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:02:18', '2015-11-29 20:02:21'),
                        (11, 1, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"msg_new_user_track\",\"label\":\" < a href = \\\"http://old-portal.dev:8080/\\\" target = \\\"_blank\\\" title = \\\"http://old-portal.dev:8080/\\\" > old - portal . dev:8080 /< / a>\"}', 1, 1, 1, X'613A333A7B733A31343A226E65775F757365725F747261636B223B733A32373A22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223B733A353A226C6162656C223B733A3131343A223C6120687265663D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F22207461726765743D225F626C616E6B22207469746C653D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223E6F6C642D706F7274616C2E6465763A383038302F3C2F613E223B733A393A227068726173655F6964223B733A31383A226D73675F6E65775F757365725F747261636B223B7D', '2015-11-29 20:02:50', NULL),

                        (12, 2, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_started\"}', 1, 1, 1, X'613A313A7B733A393A227068726173655F6964223B733A31353A226D6573736167655F73746172746564223B7D', '2015-11-29 20:03:55', NULL),
                        (13, 2, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"msg_new_user_track\",\"label\":\" < a href = \\\"http://old-portal.dev:8080/\\\" target = \\\"_blank\\\" title = \\\"http://old-portal.dev:8080/\\\" > old - portal . dev:8080 /< / a>\"}', 1, 1, 1, X'613A333A7B733A31343A226E65775F757365725F747261636B223B733A32373A22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223B733A353A226C6162656C223B733A3131343A223C6120687265663D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F22207461726765743D225F626C616E6B22207469746C653D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223E6F6C642D706F7274616C2E6465763A383038302F3C2F613E223B733A393A227068726173655F6964223B733A31383A226D73675F6E65775F757365725F747261636B223B7D', '2015-11-29 20:03:55', NULL),
                        (14, 2, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:04:00', '2015-11-29 20:04:04'),
                        (15, 2, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_assigned\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A383A7B733A31333A22636861745F61737369676E6564223B623A313B733A31313A2261737369676E65645F746F223B693A313B733A31333A2261737369676E65645F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31353A2261737369676E65645F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B733A31353A226F6C645F61737369676E65645F746F223B693A303B733A31373A226F6C645F61737369676E65645F6E616D65223B733A303A22223B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31363A226D6573736167655F61737369676E6564223B7D', '2015-11-29 20:04:00', '2015-11-29 20:04:04'),
                        (16, 2, NULL, NULL, 'user', 'Joe', 'hey would ya help me?', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:04:07', NULL),
                        (17, 2, NULL, NULL, 'user', 'Joe', 'I need some help here', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:04:10', NULL),
                        (18, 2, 1, NULL, 'agent', 'Joe', '<div>Sure, what seems to be the problem?</div>', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:04:18', '2015-11-29 20:04:23'),
                        (19, 2, NULL, NULL, 'user', 'Joe', 'I cant figure this out at all....', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:04:26', NULL),
                        (20, 2, 1, NULL, 'agent', 'Admin Admin', 'Well let me help you with that!', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:04:36', '2015-11-29 20:04:40'),

                        (21, 3, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_started\"}', 1, 1, 1, X'613A313A7B733A393A227068726173655F6964223B733A31353A226D6573736167655F73746172746564223B7D', '2015-11-29 20:09:51', NULL),
                        (22, 3, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"msg_new_user_track\",\"label\":\" < a href = \\\"http://old-portal.dev:8080/\\\" target = \\\"_blank\\\" title = \\\"http://old-portal.dev:8080/\\\" > old - portal . dev:8080 /< / a>\"}', 1, 1, 1, X'613A333A7B733A31343A226E65775F757365725F747261636B223B733A32373A22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223B733A353A226C6162656C223B733A3131343A223C6120687265663D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F22207461726765743D225F626C616E6B22207469746C653D22687474703A2F2F6F6C642D706F7274616C2E6465763A383038302F223E6F6C642D706F7274616C2E6465763A383038302F3C2F613E223B733A393A227068726173655F6964223B733A31383A226D73675F6E65775F757365725F747261636B223B7D', '2015-11-29 20:09:51', NULL),
                        (23, 3, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:09:57', '2015-11-29 20:10:00'),
                        (24, 3, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_assigned\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A383A7B733A31333A22636861745F61737369676E6564223B623A313B733A31313A2261737369676E65645F746F223B693A313B733A31333A2261737369676E65645F6E616D65223B733A31313A2241646D696E2041646D696E223B733A31353A2261737369676E65645F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B733A31353A226F6C645F61737369676E65645F746F223B693A303B733A31373A226F6C645F61737369676E65645F6E616D65223B733A303A22223B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31363A226D6573736167655F61737369676E6564223B7D', '2015-11-29 20:09:57', '2015-11-29 20:10:00'),
                        (25, 3, 1, NULL, 'agent', 'Admin Admin', '<div>oh, well hello there</div>', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:10:07', '2015-11-29 20:10:12'),
                        (26, 3, 1, NULL, 'agent', 'Admin Admin', 'this is admin can I help you', 0, 0, 1, X'613A323A7B733A31333A22706572736F6E5F617661746172223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D343026643D6D6D223B733A31383A22706572736F6E5F6176617461725F69636F6E223B733A37343A22687474703A2F2F7777772E67726176617461722E636F6D2F6176617461722F35393233356633356534373633616262306235343762643039333536326636653F26733D313626643D6D6D223B7D', '2015-11-29 20:10:28', '2015-11-29 20:10:30'),
                        (27, 3, NULL, NULL, 'user', 'Joe', 'yes, help me', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:10:33', NULL),
                        (28, 3, NULL, NULL, 'user', 'Joe', 'you should see my custom data', 0, 0, 0, X'613A313A7B733A31353A2269735F757365725F6D657373616765223B623A313B7D', '2015-11-29 20:10:39', NULL),
                        (29, 1, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:11:04', NULL),
                        (30, 2, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:11:04', NULL),
                        (31, 3, NULL, 'user_joined.1', '', 'Joe', '{\"phrase_id\":\"message_user - joined\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A353A7B733A31313A22757365725F6A6F696E6564223B623A313B733A31313A22706572736F6E5F6E616D65223B733A31313A2241646D696E2041646D696E223B733A393A22706572736F6E5F6964223B693A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31393A226D6573736167655F757365722D6A6F696E6564223B7D', '2015-11-29 20:11:05', '2015-11-29 20:11:10'),
                        (32, 3, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_ended - by\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A333A7B733A31303A22636861745F656E646564223B623A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31363A226D6573736167655F656E6465642D6279223B7D', '2015-11-29 20:14:48', NULL),
                        (33, 2, NULL, NULL, '', 'Joe', '{\"phrase_id\":\"message_ended - by\",\"name\":\"Admin Admin\"}', 1, 0, 0, X'613A333A7B733A31303A22636861745F656E646564223B623A313B733A343A226E616D65223B733A31313A2241646D696E2041646D696E223B733A393A227068726173655F6964223B733A31363A226D6573736167655F656E6465642D6279223B7D', '2015-11-29 20:14:53', NULL);
        SQL;

                $this->db->exec($sql);*/
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
