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

class Build1474294870 extends AbstractBuild
{
    public function run()
    {
        $this->out('Twilio');
        $this->execDbQuery('default', <<<'SQL'
/*db:default*/ CREATE TABLE twilio_accounts (id INT AUTO_INCREMENT NOT NULL, account_name VARCHAR(255) NOT NULL, account_sid VARCHAR(100) NOT NULL, auth_token VARCHAR(100) NOT NULL, date_created DATETIME NOT NULL, UNIQUE INDEX account_sid (account_sid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE twilio_assets (id INT AUTO_INCREMENT NOT NULL, blob_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, language LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_212CB433ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE twilio_numbers (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, target_queue_id INT DEFAULT NULL, target_agent_id INT DEFAULT NULL, sid VARCHAR(50) NOT NULL, number VARCHAR(255) NOT NULL, nickname VARCHAR(255) NOT NULL, country_code VARCHAR(10) NOT NULL, INDEX IDX_C216F8019B6B5FBA (account_id), INDEX IDX_C216F8019DD08BC7 (target_queue_id), INDEX IDX_C216F801EEBFA162 (target_agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE twilio_queues (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, routing_model VARCHAR(255) NOT NULL, max_queue_size INT NOT NULL, greetAsset_id INT DEFAULT NULL, loopAsset_id INT DEFAULT NULL, voicemail_asset_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_9737CB2B96094606 (greetAsset_id), UNIQUE INDEX UNIQ_9737CB2BDC60BD4E (loopAsset_id), UNIQUE INDEX UNIQ_9737CB2B43642E22 (voicemail_asset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE twilio_queue_agents (twilio_queue_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_FB85BAD4F4A8A9B2 (twilio_queue_id), INDEX IDX_FB85BAD4217BBB47 (person_id), PRIMARY KEY(twilio_queue_id, person_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ ALTER TABLE twilio_assets ADD CONSTRAINT FK_212CB433ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id);
/*db:default*/ ALTER TABLE twilio_numbers ADD CONSTRAINT FK_C216F8019B6B5FBA FOREIGN KEY (account_id) REFERENCES twilio_accounts (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE twilio_numbers ADD CONSTRAINT FK_C216F8019DD08BC7 FOREIGN KEY (target_queue_id) REFERENCES twilio_queues (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE twilio_numbers ADD CONSTRAINT FK_C216F801EEBFA162 FOREIGN KEY (target_agent_id) REFERENCES people (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE twilio_queues ADD CONSTRAINT FK_9737CB2B96094606 FOREIGN KEY (greetAsset_id) REFERENCES twilio_assets (id);
/*db:default*/ ALTER TABLE twilio_queues ADD CONSTRAINT FK_9737CB2BDC60BD4E FOREIGN KEY (loopAsset_id) REFERENCES twilio_assets (id);
/*db:default*/ ALTER TABLE twilio_queues ADD CONSTRAINT FK_9737CB2B43642E22 FOREIGN KEY (voicemail_asset_id) REFERENCES twilio_assets (id);
/*db:default*/ ALTER TABLE twilio_queue_agents ADD CONSTRAINT FK_FB85BAD4F4A8A9B2 FOREIGN KEY (twilio_queue_id) REFERENCES twilio_queues (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE twilio_queue_agents ADD CONSTRAINT FK_FB85BAD4217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE;
/*db:default*/ CREATE TABLE agent_data (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, extension_number INT NOT NULL, voicemail_asset_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_684980217BBB47 (person_id), UNIQUE INDEX UNIQ_68498043642E22 (voicemail_asset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ ALTER TABLE agent_data ADD CONSTRAINT FK_684980217BBB47 FOREIGN KEY (person_id) REFERENCES people (id);
/*db:default*/ ALTER TABLE agent_data ADD CONSTRAINT FK_68498043642E22 FOREIGN KEY (voicemail_asset_id) REFERENCES twilio_assets (id);
/*db:default*/ CREATE UNIQUE INDEX unique_extension_numbers ON agent_data (extension_number);
/*db:default*/ ALTER TABLE agent_data CHANGE extension_number extension_number INT DEFAULT NULL;
SQL
        );
    }
}
