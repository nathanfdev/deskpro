<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1572414478 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE approval_templates (id BIGINT AUTO_INCREMENT NOT NULL, type_id BIGINT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, approval_criteria LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', required_approvals INT NOT NULL, required_rejections INT NOT NULL, can_approvers_view_subject TINYINT(1) NOT NULL, actions_on_create LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', actions_on_partial_approval_response LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', actions_on_partial_rejection_response LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', actions_on_cancel LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', actions_on_approved LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', actions_on_rejected LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', created_at DATETIME NOT NULL, INDEX IDX_92D5AF50C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE approval_responses (id BIGINT AUTO_INCREMENT NOT NULL, approver_id INT NOT NULL, approval_id BIGINT NOT NULL, vote SMALLINT NOT NULL, message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_CCA24D4ABB23766C (approver_id), INDEX IDX_CCA24D4AFE65F000 (approval_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE approval_types (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE approvals (id BIGINT AUTO_INCREMENT NOT NULL, template_id BIGINT DEFAULT NULL, cancelled_by INT DEFAULT NULL, created_by INT NOT NULL, type_id BIGINT NOT NULL, ticket_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(60) NOT NULL, last_approved_response_at DATETIME DEFAULT NULL, last_reject_response_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, cancelled_at DATETIME DEFAULT NULL, required_approvals INT NOT NULL, required_rejections INT NOT NULL, can_approvers_view_subject TINYINT(1) NOT NULL, actions_on_create LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', actions_on_partial_approval_response LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', actions_on_partial_rejection_response LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', actions_on_cancel LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', actions_on_approved LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', actions_on_rejected LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', created_at DATETIME NOT NULL, dtype VARCHAR(60) NOT NULL, INDEX IDX_B7A4D6DE5DA0FB8 (template_id), INDEX IDX_B7A4D6DE48CCCEFB (cancelled_by), INDEX IDX_B7A4D6DEDE12AB56 (created_by), INDEX IDX_B7A4D6DEC54C8C93 (type_id), INDEX IDX_B7A4D6DE700047D2 (ticket_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE approval_approvers (approval_id BIGINT NOT NULL, person_id INT NOT NULL, INDEX IDX_99119303FE65F000 (approval_id), INDEX IDX_99119303217BBB47 (person_id), PRIMARY KEY(approval_id, person_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE approval_templates ADD CONSTRAINT FK_92D5AF50C54C8C93 FOREIGN KEY (type_id) REFERENCES approval_types (id)');
        $this->execDbQuery('default', 'ALTER TABLE approval_responses ADD CONSTRAINT FK_CCA24D4ABB23766C FOREIGN KEY (approver_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE approval_responses ADD CONSTRAINT FK_CCA24D4AFE65F000 FOREIGN KEY (approval_id) REFERENCES approvals (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD CONSTRAINT FK_B7A4D6DE5DA0FB8 FOREIGN KEY (template_id) REFERENCES approval_templates (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD CONSTRAINT FK_B7A4D6DE48CCCEFB FOREIGN KEY (cancelled_by) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD CONSTRAINT FK_B7A4D6DEDE12AB56 FOREIGN KEY (created_by) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD CONSTRAINT FK_B7A4D6DEC54C8C93 FOREIGN KEY (type_id) REFERENCES approval_types (id)');
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD CONSTRAINT FK_B7A4D6DE700047D2 FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE approval_approvers ADD CONSTRAINT FK_99119303FE65F000 FOREIGN KEY (approval_id) REFERENCES approvals (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE approval_approvers ADD CONSTRAINT FK_99119303217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
