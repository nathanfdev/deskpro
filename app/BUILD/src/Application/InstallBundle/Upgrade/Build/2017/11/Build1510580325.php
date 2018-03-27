<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1510580325 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE oauth_access_tokens (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, api_token_id INT NOT NULL, INDEX IDX_CA42527C19EB6921 (client_id), UNIQUE INDEX UNIQ_CA42527C92E52D36 (api_token_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', "CREATE TABLE oauth_clients (id INT AUTO_INCREMENT NOT NULL, sys_name VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, random_id VARCHAR(255) NOT NULL, secret VARCHAR(255) NOT NULL, context VARCHAR(255) NOT NULL, redirect_uris LONGBLOB NOT NULL COMMENT '(DC2Type:array)', allowed_grant_types LONGBLOB NOT NULL COMMENT '(DC2Type:array)', date_created DATETIME NOT NULL, is_enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_13CE8101769F5A8D (sys_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->execDbQuery('default', 'CREATE TABLE oauth_codes (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, person_id INT NOT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, redirect_uri VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_62AE2B5C5F37A13B (token), INDEX IDX_62AE2B5C19EB6921 (client_id), INDEX IDX_62AE2B5C217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE oauth_refresh_tokens (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, person_id INT NOT NULL, token VARCHAR(255) NOT NULL, expires_at INT NOT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_5AB6875F37A13B (token), INDEX IDX_5AB68719EB6921 (client_id), INDEX IDX_5AB687217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE oauth_access_tokens ADD CONSTRAINT FK_CA42527C19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
        $this->execDbQuery('default', 'ALTER TABLE oauth_access_tokens ADD CONSTRAINT FK_CA42527C92E52D36 FOREIGN KEY (api_token_id) REFERENCES api_token (id)');
        $this->execDbQuery('default', 'ALTER TABLE oauth_codes ADD CONSTRAINT FK_62AE2B5C19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
        $this->execDbQuery('default', 'ALTER TABLE oauth_codes ADD CONSTRAINT FK_62AE2B5C217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE oauth_refresh_tokens ADD CONSTRAINT FK_5AB68719EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
        $this->execDbQuery('default', 'ALTER TABLE oauth_refresh_tokens ADD CONSTRAINT FK_5AB687217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
