<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1566216031 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE approval_templates ADD actions_on_create LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', ADD actions_on_partial_approval_response LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', ADD actions_on_partial_rejection_response LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', ADD actions_on_cancel LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', ADD actions_on_approved LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', ADD actions_on_rejected LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\'');
        $this->execDbQuery('default', 'ALTER TABLE approvals ADD actions_on_create LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', ADD actions_on_partial_approval_response LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', ADD actions_on_partial_rejection_response LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', ADD actions_on_cancel LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', ADD actions_on_approved LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\', ADD actions_on_rejected LONGTEXT NOT NULL COMMENT \'(DC2Type:dp_json_obj)\'');
    }

    public function run()
    {
    }
}
