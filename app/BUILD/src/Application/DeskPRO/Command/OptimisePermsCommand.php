<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\People\PermissionUtil;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OptimisePermsCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:optimise-perms');
        $this->addOption('person-id', 'p', InputOption::VALUE_REQUIRED, 'Process a specific agent');
        $this->setHelp('Processes permissions tables to optimise permission resolving for helpdesks using many agents');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = App::getDb();

        $count_before = $this->countPerms();
        $t            = microtime(true);

        $person_id = $input->getOption('person-id');
        $agents    = App::$container->getAgentData()->getAgents();

        if (!$person_id) {
            $ag_perms_cache     = null;
            $ag_dep_perms_cache = null;
        } else {
            $ag_perms_cache     = $db->fetchAllGrouped('SELECT usergroup_id, name FROM permissions', [], 'usergroup_id', null, 'name');
            $ag_dep_perms_cache = [
                'full'   => $db->fetchAllGrouped("SELECT usergroup_id, department_id FROM department_permissions WHERE name = 'full'", [], 'usergroup_id', null, 'department_id'),
                'assign' => $db->fetchAllGrouped("SELECT usergroup_id, department_id FROM department_permissions WHERE name = 'assign'", [], 'usergroup_id', null, 'department_id'),
            ];
        }

        foreach ($agents as $a) {
            if ($person_id && $a->getId() != $person_id) {
                continue;
            }

            $output->write("Optimising Agent #{$a->getId()} {$a->getDisplayContact()} ... ");
            PermissionUtil::optimizePermissions($a, $ag_perms_cache, $ag_dep_perms_cache);
            $output->writeln('Done');
        }

        // Also delete perms that are applied to a parent departments
        $parent_dep_ids = $db->fetchAllCol('SELECT DISTINCT(parent_id) FROM departments');
        if ($parent_dep_ids) {
            $db->deleteIn('department_permissions', $parent_dep_ids, 'department_id');
        }

        $t2          = microtime(true);
        $count_after = $this->countPerms();

        $output->writeln(sprintf("Permission records before: {$count_before['all']}"));
        $output->writeln(sprintf("Permission records after:  {$count_after['all']}"));
        $output->writeln(sprintf('Optimised in %0.3fs', $t2 - $t));

        return 0;
    }

    private function countPerms()
    {
        $db = App::getDb();

        $counts = [
            'perms'     => $db->fetchColumn('SELECT COUNT(*) FROM permissions WHERE is_active = 1'),
            'dep_perms' => $db->fetchColumn('SELECT COUNT(*) FROM department_permissions WHERE is_active = 1'),
        ];

        $counts['all'] = $counts['perms'] + $counts['dep_perms'];

        return $counts;
    }
}
