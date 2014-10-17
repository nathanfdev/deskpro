<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\InstallBundle\Util\GenBuildManifest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenBuildClassCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dpdev:gen-build-class');
		$this->addOption('out', null, InputOption::VALUE_NONE, 'Output code instead of writing it');
		$this->addOption('no-schema', null, InputOption::VALUE_NONE, 'Do not try to auto-detect schema diff');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$time = time();

		if (!$input->getOption('no-schema')) {
			$diff = \Application\DeskPRO\ORM\Util\Util::getUpdateSchemaSql(App::getOrm());
		} else {
			$diff = array();
		}

		if ($diff) {
			$defaultcode = array();

			foreach ($diff as $sql) {
				$sql = str_replace("\\'", "'", addslashes($sql));
				$sql = str_replace('$', '\\$', $sql);
				$defaultcode[] = "\t\t\$this->execMutateSql(\"".$sql."\");";
			}

			$defaultcode = implode("\n", $defaultcode);

		} else {
			$defaultcode = "\t\t//\$this->execMutateSql(\"...\");";
		}

		$tpl = <<<CODE
<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build$time extends AbstractBuild
{
	public function run()
	{
		\$this->out("My Upgrade Class");
$defaultcode
	}
}
CODE;

		$path_dir = DP_ROOT . "/src/Application/InstallBundle/Upgrade/Build/" . date('Y/m', $time);

		if (!is_dir($path_dir)) {
			mkdir($path_dir, 0744, true);
		}

		$path = "$path_dir/Build$time.php";

		if ($input->getOption('out')) {
			echo $tpl;
			echo "\n";
			echo "!!! For this build to be active, it must be added to the build-manifest.php file !!!\n\n";
		} else {
			file_put_contents($path, $tpl);

			$build_file = DP_ROOT.'/sys/config/build-time.php';
			file_put_contents($build_file, '<?php define("DP_BUILD_TIME", '.$time.'); ');

			$manifest_path = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/build-manifest.php';
			$builds_path   = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build';

			$gen = new GenBuildManifest($builds_path, array($path));
			file_put_contents($manifest_path, $gen->getContents());

			echo "Wrote file: $path\n";
			echo "Updated: $manifest_path\n";
			echo "Updated: $build_file\n";
		}

		return 0;
	}
}