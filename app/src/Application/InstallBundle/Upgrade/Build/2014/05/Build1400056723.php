<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

class Build1400056723 extends AbstractBuild
{
    public function run()
    {
        $db = $this->container->getDb();

        $this->out("Re-create search tables");
        $db->exec("CREATE TABLE tickets_search_active (id INT NOT NULL, ref VARCHAR(100) NOT NULL, language_id INT DEFAULT NULL, department_id INT DEFAULT NULL, category_id INT DEFAULT NULL, workflow_id INT DEFAULT NULL, priority_id INT DEFAULT NULL, product_id INT DEFAULT NULL, person_id INT DEFAULT NULL, person_email_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, agent_team_id INT DEFAULT NULL, organization_id INT DEFAULT NULL, sent_to_address VARCHAR(200) NOT NULL, creation_system VARCHAR(100) NOT NULL, creation_system_option VARCHAR(1000) NOT NULL, status VARCHAR(30) NOT NULL, is_hold TINYINT(1) NOT NULL, urgency INT NOT NULL, feedback_rating INT DEFAULT NULL, date_feedback_rating DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, date_resolved DATETIME DEFAULT NULL, date_first_agent_assign DATETIME DEFAULT NULL, date_first_agent_reply DATETIME DEFAULT NULL, date_last_agent_reply DATETIME DEFAULT NULL, date_last_user_reply DATETIME DEFAULT NULL, date_agent_waiting DATETIME DEFAULT NULL, date_user_waiting DATETIME DEFAULT NULL, date_status DATETIME NOT NULL, total_user_waiting INT NOT NULL, total_to_first_reply INT NOT NULL, subject VARCHAR(255) NOT NULL, original_subject VARCHAR(255) NOT NULL, INDEX date_created_idx (date_created), INDEX status_idx (status), INDEX person_idx (person_id), INDEX agent_idx (agent_id), INDEX ref_idx (ref), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }
}
