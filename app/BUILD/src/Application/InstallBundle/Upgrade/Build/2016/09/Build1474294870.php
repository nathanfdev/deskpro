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
/*db:default*/ CREATE TABLE agent_data (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, voicemail_asset_id INT DEFAULT NULL, extension_number INT DEFAULT NULL, UNIQUE INDEX UNIQ_684980217BBB47 (person_id), UNIQUE INDEX UNIQ_6849807D165057 (voicemail_asset_id), UNIQUE INDEX unique_extension_numbers (extension_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE voice_accounts (id INT AUTO_INCREMENT NOT NULL, account_name VARCHAR(255) NOT NULL, account_sid VARCHAR(100) NOT NULL, auth_token VARCHAR(100) NOT NULL, date_created DATETIME NOT NULL, UNIQUE INDEX account_sid (account_sid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE voice_assets (id INT AUTO_INCREMENT NOT NULL, blob_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, language LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_BE17F9F2ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE voice_auto_attendants (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, allow_repeat_menu TINYINT(1) NOT NULL, allow_extension TINYINT(1) NOT NULL, audioAsset_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_94EFE731B75393B6 (audioAsset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE voice_auto_attendant_dial_numbers (id INT AUTO_INCREMENT NOT NULL, voice_auto_attendant_id INT DEFAULT NULL, target_id INT DEFAULT NULL, dial_num INT NOT NULL, INDEX IDX_6048AB39765D3C52 (voice_auto_attendant_id), UNIQUE INDEX UNIQ_6048AB39158E0B66 (target_id), UNIQUE INDEX dial_nums_unique_idx (voice_auto_attendant_id, dial_num), UNIQUE INDEX target_unique_idx (voice_auto_attendant_id, target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE voice_numbers (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, target_id INT DEFAULT NULL, sid VARCHAR(50) NOT NULL, number VARCHAR(255) NOT NULL, nickname VARCHAR(255) NOT NULL, country_code VARCHAR(10) NOT NULL, INDEX IDX_2EEA316A9B6B5FBA (account_id), UNIQUE INDEX UNIQ_2EEA316A158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE voice_queues (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, routing_model VARCHAR(255) NOT NULL, max_queue_size INT NOT NULL, greetAsset_id INT DEFAULT NULL, loopAsset_id INT DEFAULT NULL, voicemailAsset_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_80C86EA96094606 (greetAsset_id), UNIQUE INDEX UNIQ_80C86EADC60BD4E (loopAsset_id), UNIQUE INDEX UNIQ_80C86EA43642E22 (voicemailAsset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE voice_queue_agents (voice_queue_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_50376B502E24EDAB (voice_queue_id), INDEX IDX_50376B50217BBB47 (person_id), PRIMARY KEY(voice_queue_id, person_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ CREATE TABLE voice_targets (id INT AUTO_INCREMENT NOT NULL, voice_queue_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, voice_auto_attendant_id INT DEFAULT NULL, type VARCHAR(30) NOT NULL, INDEX IDX_F637E5802E24EDAB (voice_queue_id), INDEX IDX_F637E5803414710B (agent_id), INDEX IDX_F637E580765D3C52 (voice_auto_attendant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*db:default*/ ALTER TABLE agent_data ADD CONSTRAINT FK_684980217BBB47 FOREIGN KEY (person_id) REFERENCES people (id);
/*db:default*/ ALTER TABLE agent_data ADD CONSTRAINT FK_6849807D165057 FOREIGN KEY (voicemail_asset_id) REFERENCES voice_assets (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE voice_assets ADD CONSTRAINT FK_BE17F9F2ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id);
/*db:default*/ ALTER TABLE voice_auto_attendants ADD CONSTRAINT FK_94EFE731B75393B6 FOREIGN KEY (audioAsset_id) REFERENCES voice_assets (id);
/*db:default*/ ALTER TABLE voice_auto_attendant_dial_numbers ADD CONSTRAINT FK_6048AB39765D3C52 FOREIGN KEY (voice_auto_attendant_id) REFERENCES voice_auto_attendants (id);
/*db:default*/ ALTER TABLE voice_auto_attendant_dial_numbers ADD CONSTRAINT FK_6048AB39158E0B66 FOREIGN KEY (target_id) REFERENCES voice_targets (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE voice_numbers ADD CONSTRAINT FK_2EEA316A9B6B5FBA FOREIGN KEY (account_id) REFERENCES voice_accounts (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE voice_numbers ADD CONSTRAINT FK_2EEA316A158E0B66 FOREIGN KEY (target_id) REFERENCES voice_targets (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EA96094606 FOREIGN KEY (greetAsset_id) REFERENCES voice_assets (id);
/*db:default*/ ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EADC60BD4E FOREIGN KEY (loopAsset_id) REFERENCES voice_assets (id);
/*db:default*/ ALTER TABLE voice_queues ADD CONSTRAINT FK_80C86EA43642E22 FOREIGN KEY (voicemailAsset_id) REFERENCES voice_assets (id);
/*db:default*/ ALTER TABLE voice_queue_agents ADD CONSTRAINT FK_50376B502E24EDAB FOREIGN KEY (voice_queue_id) REFERENCES voice_queues (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE voice_queue_agents ADD CONSTRAINT FK_50376B50217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE voice_targets ADD CONSTRAINT FK_F637E5802E24EDAB FOREIGN KEY (voice_queue_id) REFERENCES voice_queues (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE voice_targets ADD CONSTRAINT FK_F637E5803414710B FOREIGN KEY (agent_id) REFERENCES people (id) ON DELETE CASCADE;
/*db:default*/ ALTER TABLE voice_targets ADD CONSTRAINT FK_F637E580765D3C52 FOREIGN KEY (voice_auto_attendant_id) REFERENCES voice_auto_attendants (id) ON DELETE CASCADE;
SQL
        );
    }
}
