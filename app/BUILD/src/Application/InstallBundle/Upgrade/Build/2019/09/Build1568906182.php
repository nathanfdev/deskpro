<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1568906182 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }


    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE approval_templates ADD approver_selection_criteria LONGTEXT COMMENT \'(DC2Type:dp_json_obj)\', CHANGE approval_criteria selected_approvers LONGTEXT COMMENT \'(DC2Type:dp_json_obj)\'');
        $this->execDbQuery('default', 'ALTER TABLE approval_templates ADD can_choose_approvers TINYINT NOT NULL');
    }

    public function run()
    {
    }
}
