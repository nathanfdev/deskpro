<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1482402882 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add encryption fields to email accounts');

        $this->execDbQuery('default', 'ALTER TABLE email_accounts ADD cert_blob_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE email_accounts ADD CONSTRAINT FK_C1AE81E5A63D3520 FOREIGN KEY (cert_blob_id) REFERENCES blobs (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_C1AE81E5A63D3520 ON email_accounts (cert_blob_id)');

        $this->execDbQuery('default', 'ALTER TABLE email_accounts ADD key_blob_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE email_accounts ADD CONSTRAINT FK_C1AE81E58FE8AA9B FOREIGN KEY (key_blob_id) REFERENCES blobs (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_C1AE81E58FE8AA9B ON email_accounts (key_blob_id)');

        $this->execDbQuery('default', 'ALTER TABLE email_accounts ADD key_pass_phrase VARCHAR(255) DEFAULT NULL');
    }
}
