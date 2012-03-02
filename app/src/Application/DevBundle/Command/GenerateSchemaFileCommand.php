<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @category Commands
 */

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;


/**
 * dpdev:generate-schema-file
 */
class GenerateSchemaFileCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
			new InputOption('save', 'w', InputOption::VALUE_NONE, 'Save to the InstallBundle directory instead of outputting'),
		))->setName('dpdev:generate-schema-file');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$em = $this->getContainer()->get('doctrine.orm.entity_manager');
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
		$all_sql = $tool->getCreateSchemaSql($metadata);

		$php_creates = array();
		$php_alters  = array();

		$xa = 0;
		$xc = 0;
		foreach ($all_sql as $s) {
			$s = trim($s);
			$s_ex = var_export($s, true);

			if (preg_match('#^ALTER#', $s)) {
				$php_alters[] = "\$queries['alter'][$xa] = $s_ex;";
				$xa++;
			} else {
				$php_creates[] = "\$queries['create'][$xc] = $s_ex;";
				$xc++;
			}
		}

		$s = <<<SQL
CREATE TABLE `content_search` (
  `object_type` varchar(15) NOT NULL DEFAULT '',
  `object_id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  PRIMARY KEY (`object_type`,`object_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

		$s_ex = var_export($s, true);
		$php_creates[] = "\$queries['create'][$xc] = $s_ex;";
		$xc++;

		$php = "<?php\n\n\$queries = array('create' => array(), 'alter' => array());\n\n";
		$php .= implode("\n", $php_creates);
		$php .= "\n\n\n\n\n";
		$php .= implode("\n", $php_alters);
		$php .= "\n\n\n\n\nreturn \$queries;\n";

		$do_save = $input->getOption('save');
		if ($do_save) {
			file_put_contents(DP_ROOT.'/src/Application/InstallBundle/Data/schema.php', $php);
		} else {
			echo $php;
		}
	}
}
