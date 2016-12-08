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

namespace Application\InstallBundle\Upgrade\Build;

class Build1481197257 extends AbstractBuild
{
    public function run()
    {
        $this->out('My Upgrade Class');
        $this->execDbQuery('default', 'CREATE TABLE ticket_message_attributes (id INT AUTO_INCREMENT NOT NULL, ticket_message_id INT DEFAULT NULL, name VARCHAR(250) NOT NULL, value VARCHAR(5000) DEFAULT NULL, date_created DATETIME NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_92C49E9BC5E9817D (ticket_message_id), UNIQUE INDEX attr_name (ticket_message_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $this->execDbQuery('default', 'ALTER TABLE ticket_message_attributes ADD CONSTRAINT FK_92C49E9BC5E9817D FOREIGN KEY (ticket_message_id) REFERENCES tickets_messages (id) ON DELETE CASCADE');
    }
}
