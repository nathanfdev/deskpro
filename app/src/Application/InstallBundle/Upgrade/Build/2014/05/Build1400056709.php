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

class Build1400056709 extends AbstractBuild
{
    public function run()
    {
        $db = $this->container->getDb();

        $this->out("Modify indexes and FKs to new app tables");

        // Remove keys
        /* [FK]  plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_article DROP FOREIGN KEY FK_B651E6F4EC942BCF", true);
        /* [IDX] plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_article DROP KEY IDX_B651E6F4EC942BCF", true);
        /* [FK]  plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_chat DROP FOREIGN KEY FK_2DE86CE5EC942BCF", true);
        /* [IDX] plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_chat DROP KEY IDX_2DE86CE5EC942BCF", true);
        /* [FK]  plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_feedback DROP FOREIGN KEY FK_CC9CDDD8EC942BCF", true);
        /* [IDX] plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_feedback DROP KEY IDX_CC9CDDD8EC942BCF", true);
        /* [FK]  plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_organizations DROP FOREIGN KEY FK_240601E7EC942BCF", true);
        /* [IDX] plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_organizations DROP KEY IDX_240601E7EC942BCF", true);
        /* [FK]  plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_people DROP FOREIGN KEY FK_4840CFDAEC942BCF", true);
        /* [IDX] plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_people DROP KEY IDX_4840CFDAEC942BCF", true);
        /* [FK]  plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_products DROP FOREIGN KEY FK_AD0FC3DAEC942BCF", true);
        /* [IDX] plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_products DROP KEY IDX_AD0FC3DAEC942BCF", true);
        /* [FK]  plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_ticket DROP FOREIGN KEY FK_F7F6085FEC942BCF", true);
        /* [IDX] plugin_id */ $this->execMutateSql("ALTER TABLE custom_def_ticket DROP KEY IDX_F7F6085FEC942BCF", true);
        /* [FK]  plugin_id */ $this->execMutateSql("ALTER TABLE ticket_trigger_plugin_actions DROP FOREIGN KEY FK_1D905890EC942BCF", true);
        /* [IDX] plugin_id */ $this->execMutateSql("ALTER TABLE ticket_trigger_plugin_actions DROP KEY IDX_1D905890EC942BCF", true);
        /* [FK]  plugin_id */ $this->execMutateSql("ALTER TABLE usersource_plugins DROP FOREIGN KEY FK_E484A367EC942BCF", true);
        /* [IDX] plugin_id */ $this->execMutateSql("ALTER TABLE usersource_plugins DROP KEY IDX_E484A367EC942BCF", true);

        // Remove fields
        $db->exec("ALTER TABLE custom_def_article DROP plugin_id");
        $db->exec("ALTER TABLE custom_def_chat DROP plugin_id");
        $db->exec("ALTER TABLE custom_def_feedback DROP plugin_id");
        $db->exec("ALTER TABLE custom_def_organizations DROP plugin_id");
        $db->exec("ALTER TABLE custom_def_people DROP plugin_id");
        $db->exec("ALTER TABLE custom_def_products DROP plugin_id");
        $db->exec("ALTER TABLE custom_def_ticket DROP plugin_id");
        $db->exec("ALTER TABLE ticket_trigger_plugin_actions DROP plugin_id");
        $db->exec("ALTER TABLE usersource_plugins DROP plugin_id");

        // Add fields
        $db->exec("ALTER TABLE custom_def_article ADD app_id INT DEFAULT NULL");
        $db->exec("ALTER TABLE custom_def_chat ADD app_id INT DEFAULT NULL");
        $db->exec("ALTER TABLE custom_def_feedback ADD app_id INT DEFAULT NULL");
        $db->exec("ALTER TABLE custom_def_organizations ADD app_id INT DEFAULT NULL");
        $db->exec("ALTER TABLE custom_def_people ADD app_id INT DEFAULT NULL");
        $db->exec("ALTER TABLE custom_def_products ADD app_id INT DEFAULT NULL");
        $db->exec("ALTER TABLE custom_def_ticket ADD app_id INT DEFAULT NULL");

        // Add keys
        $db->exec("ALTER TABLE custom_def_article ADD CONSTRAINT FK_B651E6F47987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
        $db->exec("CREATE INDEX IDX_B651E6F47987212D ON custom_def_article (app_id)");
        $db->exec("ALTER TABLE custom_def_chat ADD CONSTRAINT FK_2DE86CE57987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
        $db->exec("CREATE INDEX IDX_2DE86CE57987212D ON custom_def_chat (app_id)");
        $db->exec("ALTER TABLE custom_def_feedback ADD CONSTRAINT FK_CC9CDDD87987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
        $db->exec("CREATE INDEX IDX_CC9CDDD87987212D ON custom_def_feedback (app_id)");
        $db->exec("ALTER TABLE custom_def_organizations ADD CONSTRAINT FK_240601E77987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
        $db->exec("CREATE INDEX IDX_240601E77987212D ON custom_def_organizations (app_id)");
        $db->exec("ALTER TABLE custom_def_people ADD CONSTRAINT FK_4840CFDA7987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
        $db->exec("CREATE INDEX IDX_4840CFDA7987212D ON custom_def_people (app_id)");
        $db->exec("ALTER TABLE custom_def_products ADD CONSTRAINT FK_AD0FC3DA7987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
        $db->exec("CREATE INDEX IDX_AD0FC3DA7987212D ON custom_def_products (app_id)");
        $db->exec("ALTER TABLE custom_def_ticket ADD CONSTRAINT FK_F7F6085F7987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE SET NULL");
        $db->exec("CREATE INDEX IDX_F7F6085F7987212D ON custom_def_ticket (app_id)");
    }
}
