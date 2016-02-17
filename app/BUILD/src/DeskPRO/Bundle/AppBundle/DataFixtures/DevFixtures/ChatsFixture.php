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

use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class ChatsFixture extends DeskProAbstractFixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadCustomDefChat();
    }

    private function loadCustomDefChat()
    {
        $sql = <<<SQL
            INSERT INTO `custom_def_chat` (`id`, `parent_id`, `app_id`, `js_class`, `has_form_template`, `has_display_template`, `title`, `description`, `handler_class`, `options`, `is_user_enabled`, `is_enabled`, `display_order`, `default_value`, `is_agent_field`)
            VALUES
              (1, NULL, NULL, '', 0, 0, 'Chat text', 'this is a text box for a chat', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Text', X'613A303A7B7D', 1, 1, 0, NULL, 0),
              (2, NULL, NULL, '', 0, 0, 'chatt toggle it\'', 'this is a toggle for chat', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Toggle', X'613A303A7B7D', 1, 1, 0, '', 0);
SQL;

        $this->db->exec($sql);
    }


}
