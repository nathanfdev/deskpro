<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1421977484 extends AbstractBuild
{
    public function run()
    {
        $this->out("Add default feedback status");
        // high display_order
		$this->execMutateSql("
          INSERT INTO feedback_status_categories (status_type, title, display_order) VALUES ('active','Gathering Feedback',2001)
        ");

        $default_status_category = $this->container->getDb()->lastInsertId();

        $this->execMutateSql("
          UPDATE feedback SET status_category_id = ".$default_status_category.", status = 'active' WHERE status = 'new'
        ");


        $this->execMutateSql("REPLACE INTO settings SET name = 'portal.default_feedback_status_category_id', value = ".$default_status_category);
    }
}